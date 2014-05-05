package infoarena;

import java.io.BufferedInputStream;
import java.io.BufferedOutputStream;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.PrintStream;
import java.io.PrintWriter;
import java.io.StringWriter;
import java.lang.management.ManagementFactory;
import java.lang.management.MemoryMXBean;
import java.lang.management.ThreadInfo;
import java.lang.management.ThreadMXBean;
import java.lang.reflect.InvocationTargetException;
import java.lang.reflect.Method;
import java.lang.reflect.Modifier;
import java.util.Scanner;

import sun.misc.Signal;
import sun.misc.SignalHandler;

public class InfoarenaJudge {
    private static PrintStream realOutput = System.out;

    private static MemoryMXBean memoryBean = ManagementFactory.getMemoryMXBean();

    private static ThreadMXBean threadBean = ManagementFactory.getThreadMXBean();

    private static int memoryUsed = 0;
    private static int baseMemoryUsed = 0;
    private static int timeUsed = 0;

    private static int uid;
    private static int gid;

    private static int memoryLimit;
    private static int timeLimit;

    private static String[] permittedFiles;

    private static Thread childThread;

    private static void loadChildThread() {
        childThread = new Thread() {
            public InfoarenaSecurityManager securityManager = new InfoarenaSecurityManager(permittedFiles);

            private Method mainMethod = loadMainMethod();

            public void run() {
                Signal.handle(new Signal("XCPU"), new SignalHandler() {
                    @Override
                    public void handle(Signal sig) {
                        if (InfoarenaSecurityManager.childThread != null) {
                            InfoarenaSecurityManager.childThread = null;
                            timeUsed = timeLimit + 1;
                            printResult("FAIL", "Time Limit Exceeded");
                        }
                    }
                });

                if (setLimits(timeLimit, 20, uid, gid) < 0) {
                    printResult("ERROR", "Eroare de evaluator");
                }

                mainMethod.setAccessible(true);
                System.setSecurityManager(this.securityManager);
                InfoarenaSecurityManager.childThread = this;
                try {
                    Object[] arguments = new Object[] { new String[0] };
                    mainMethod.invoke(null, arguments);
                    InfoarenaSecurityManager.childThread = null;
                    updateUsages();
                } catch (InvocationTargetException e) {
                    InfoarenaSecurityManager.childThread = null;
                    Throwable targetException = e.getTargetException();
                    if (targetException instanceof OutOfMemoryError) {
                        memoryUsed = memoryLimit + 1;
                        printResult("FAIL", "Memory Limit exceeded");
                    } else {
                        printException(e);
                        printResult("FAIL", "Runtime Error");
                    }
                } catch (Exception e) {
                    InfoarenaSecurityManager.childThread = null;
                    printResult("ERROR", "Eroare de evaluator");
                }
            }
        };
    }

    public static Method loadMainMethod() {
        InfoarenaClassLoader classLoader = new InfoarenaClassLoader();

        Class<?> targetClass = null;
        try {
            for (File f : new File(".").listFiles()) {
                String name = f.getName();
                if (name.endsWith(".class")) {
                    // we preload all classes
                    Class<?> c = Class.forName(name.substring(0, name.length() - 6), false, classLoader);
                    if (name.equals("Main.class")) {
                        targetClass = c;
                    }
                }
            }
        } catch (ClassNotFoundException e) {
            printException(e);
            printResult("FAIL", "Runtime Error");
        } catch (NoClassDefFoundError e) {
            printResult("FAIL", "Runtime Error");
        } catch (ClassFormatError e) {
            printResult("ERROR", "Eroare de Evaluator");
        } catch (ExceptionInInitializerError e) {
            printResult("FAIL", "Runtime Error");
        } catch (LinkageError e) {
            printResult("FAIL", "Runtime Error");
        }

        if (targetClass == null) {
            printResult("ERROR", "Eroare de Evaluator lipseste sursa");
        }

        Method mainMethod = null;
        try {
            mainMethod = targetClass.getMethod("main", String[].class);
        } catch (NoSuchMethodException e) {
            printResult("FAIL", "main method missing");
        }
        if (!Modifier.isStatic(mainMethod.getModifiers())) {
            printResult("FAIL", "main is not static");
        }

        return mainMethod;
    }

    public static void main(String[] args) {
        if (args.length < 5) {
            printHelp();
            return;
        }

        System.load(args[0]);

        try {
            // we preload the Scanner, otherwise when it tries to get the current locale the user program gets a SecurityException
            Scanner scanner = new Scanner(args[1]);
            timeLimit = scanner.nextInt();
            memoryLimit = Integer.parseInt(args[2]);
            uid = Integer.parseInt(args[3]);
            gid = Integer.parseInt(args[4]);

            permittedFiles = new String[args.length - 5];
            for (int i = 5; i < args.length; ++i)
                permittedFiles[i - 5] = args[i];

            System.setIn(new BufferedInputStream(new FileInputStream("/dev/null")));
            System.setOut(new PrintStream(new BufferedOutputStream(new FileOutputStream("/dev/null"))));
            System.setErr(new PrintStream(new BufferedOutputStream(new FileOutputStream("/dev/null"))));
        } catch (Exception e) {
            printException(e);
        }

        System.gc();
        baseMemoryUsed = (int)memoryBean.getHeapMemoryUsage().getUsed();

        loadChildThread();
        childThread.start();
        try {
            childThread.join();
        } catch (InterruptedException e) {
            printException(e);
        }

        for (;;) {
            Thread.State state;
            ThreadInfo info = threadBean.getThreadInfo(childThread.getId());
            if (info == null)
                state = Thread.State.TERMINATED;
            else
                state = info.getThreadState();

            if (state == Thread.State.RUNNABLE || state == Thread.State.NEW || state == Thread.State.TERMINATED) {
                updateUsages();
                if (state == Thread.State.TERMINATED)
                    break;
            } else if (InfoarenaSecurityManager.childThread != null) {
                updateUsages();
                printResult("FAIL", "Runtime Error");
            }

            try {
                childThread.join(100);
            } catch (InterruptedException e) {
                printResult("OK", "Execution successful");
                break;
            }
        }

        updateUsages();
        printResult("OK", "Execution successful");
    }

    private static void printHelp() {
        System.out.println("Usage: java -jar InfoarenaJudge.jar path/to/InfoarenaJudge.so timeLimit memoryLimit uid gid allowed_file1 [allowed_file2] ...");
    }

    private synchronized static void printException(Exception e) {
        StringWriter string = new StringWriter();
        PrintWriter printer = new PrintWriter(string);
        e.printStackTrace(printer);
        realOutput.print(string.toString());
        realOutput.flush();
        Runtime.getRuntime().halt(0);
    }

    private static void updateUsages() {
        int memory = (int)memoryBean.getHeapMemoryUsage().getUsed() - baseMemoryUsed;
        if (memory > memoryUsed)
            memoryUsed = memory;
        if (childThread != null) {
            long time = (int)threadBean.getThreadCpuTime(childThread.getId()) / 1000000;
            if (time >= 0) {
                if (time > timeUsed)
                    timeUsed = (int)time;
            }
        }

        if (memoryUsed > memoryLimit * 1024)
            printResult("FAIL", "Memory Limit Exceeded");
        if (timeUsed > timeLimit)
            printResult("FAIL", "Time Limit Exceeded");
    }

    private synchronized static void printResult(String status, String message) {
        realOutput.println(status + ": time " + timeUsed + "ms memory " + ((int)memoryUsed / 1024) + "kb: " + message);
        realOutput.flush();
        Runtime.getRuntime().halt(0);
    }

    private static native int setLimits(int timeLimit, int fileLimit, int uid, int gid);
}
