InfoarenaJudge.jar: infoarena/InfoarenaJudge.java infoarena/InfoarenaClassLoader.java infoarena/InfoarenaSecurityManager.java InfoarenaJudge.so
	mkdir temp
	javac -d temp infoarena/InfoarenaJudge.java infoarena/InfoarenaClassLoader.java infoarena/InfoarenaSecurityManager.java
	javah -classpath temp -jni -force infoarena.InfoarenaJudge
	jar cfe InfoarenaJudge.jar infoarena.InfoarenaJudge -C temp .
	rm -rf temp

InfoarenaJudge.so: infoarena/InfoarenaJudge.cpp
	gcc -shared -Wl,-soname,InfoarenaJudge.so -I/usr/lib/jvm/java-7-openjdk-amd64/include/ -I/usr/lib/jvm/java-7-openjdk-amd64/include/linux -o InfoarenaJudge.so infoarena/InfoarenaJudge.cpp -fPIC
