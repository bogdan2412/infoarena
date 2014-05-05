package infoarena;

import java.security.Permission;
import java.security.SecurityPermission;
import java.util.PropertyPermission;
import java.io.FilePermission;

public class InfoarenaSecurityManager extends SecurityManager {
    private final String[] permittedFiles;

    InfoarenaSecurityManager(String[] permittedFiles) {
        this.permittedFiles = permittedFiles;
    }

    public static Thread childThread;

    @Override
    public void checkPermission(Permission permission) {
        if (Thread.currentThread() != childThread)
            return;
        if (permission instanceof SecurityPermission)
            if (permission.getName().startsWith("getProperty"))
                return;
        if (permission instanceof PropertyPermission)
            if (permission.getActions().equals("read"))
                return;

        if (permission instanceof FilePermission) {
            String filePath = permission.getName();
            for (String permittedFilePath : permittedFiles)
                if (filePath.equals(permittedFilePath))
                    return;
        }

        throw new SecurityException(permission.toString());
    }

    @Override
    public void checkPermission(Permission permission, Object context) {
        this.checkPermission(permission);
    }
}
