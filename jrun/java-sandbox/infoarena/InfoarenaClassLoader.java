package infoarena;

import java.io.File;
import java.io.FileInputStream;

class InfoarenaClassLoader extends ClassLoader {
    @Override
    public Class<?> loadClass(String name) throws ClassNotFoundException {
        if (!this.isAllowedClassName(name)) {
            throw new ClassNotFoundException(name);
        }

        return super.loadClass(name);
    }

    // This doesn't let you import your own packages
    @Override
    protected Class<?> findClass(String name) throws ClassNotFoundException {
        if (name.indexOf('.') >= 0) {
            throw new ClassNotFoundException(name);
        }
        File classFile = new File(name + ".class");
        byte[] classContent = new byte[(int) classFile.length()];
        try {
            FileInputStream in = new FileInputStream(classFile);
            in.read(classContent);
            in.close();
        } catch (Exception e) {
            e.printStackTrace();
            throw new ClassNotFoundException(name);
        }

        return this.defineClass(name, classContent, 0, classContent.length);
    }

    private boolean isAllowedClassName(String name) {
        if (name.startsWith("java.lang.")) {
            if (name.indexOf('.', 10) >= 0 || name.endsWith(".ClassNotFoundException")) {
                return false;
            }
        } else if (!name.startsWith("java.util.") && !name.startsWith("java.io.") &&
            !name.startsWith("java.nio.") && !name.startsWith("java.net.") && !name.startsWith("java.math.") &&
            !name.startsWith("java.text.") && name.indexOf('.') >= 0) {
            return false;
        }
        return true;
    }
}
