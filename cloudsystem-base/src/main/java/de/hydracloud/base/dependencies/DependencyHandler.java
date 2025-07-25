package de.hydracloud.base.dependencies;

import java.io.File;
import java.io.IOException;
import java.lang.reflect.InvocationTargetException;
import java.net.MalformedURLException;
import java.net.URI;
import java.net.URL;
import java.nio.file.Files;
import java.util.EnumMap;
import java.util.HashMap;
import java.util.Map;

public final class DependencyHandler {
    private static final Map<Dependency, String> DEPENDENCY_VERSIONS;
    static {
        DEPENDENCY_VERSIONS = new HashMap<>();
        DEPENDENCY_VERSIONS.put(Dependency.GSON, "2.9.0");
        DEPENDENCY_VERSIONS.put(Dependency.JLINE, "3.21.0");
        DEPENDENCY_VERSIONS.put(Dependency.JANSI, "2.4.0");
        DEPENDENCY_VERSIONS.put(Dependency.COMMONS_IO, "2.11.0");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_TRANSPORT, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_COMMON, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_BUFFER, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_RESOLVER, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_TRANSPORT_EPOLL, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_UNIX_COMMON, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.NETTY_CODEC, "4.1.75.Final");
        DEPENDENCY_VERSIONS.put(Dependency.MYSQL_DRIVER, "8.0.28");
        DEPENDENCY_VERSIONS.put(Dependency.MONGO_DRIVER, "3.12.10");
        DEPENDENCY_VERSIONS.put(Dependency.H2_DATABASE, "2.1.210");
    }

    private final File librariesDirectory;
    private final Map<Dependency, File> loadedDependencies;

    public DependencyHandler() {
        this.librariesDirectory = new File("libraries");
        try {
            if (!this.librariesDirectory.exists()) {
                Files.createDirectory(this.librariesDirectory.toPath());
            }
        } catch (IOException e) {
            e.printStackTrace();
        }
        this.loadedDependencies = new EnumMap<>(Dependency.class);
    }

    public void loadDependencies(final Dependency... dependencies) {
        for (final var dependency : dependencies) this.loadDependency(dependency);
    }

    public void loadDependency(final Dependency dependency) {
        final var version = DEPENDENCY_VERSIONS.get(dependency);
        if (version == null) {
            throw new IllegalArgumentException("Version for dependency " + dependency.name() + " not found.");
        }

        final var file = new File(this.librariesDirectory, dependency.getFileName(version));

        if (!file.exists()) {
            try {
                final var urlConnection = URI.create("https://repo1.maven.org/maven2/" + dependency.getMavenRepoPath(version)).toURL()
                    .openConnection();
                try (final var inputStream = urlConnection.getInputStream()) {
                    Files.write(file.toPath(), inputStream.readAllBytes());
                }
            } catch (IOException e) {
                e.printStackTrace();
            }
        }

        try {
            final var method = this.getClass().getClassLoader().getClass().getMethod("addURL", URL.class);
            method.setAccessible(true);
            method.invoke(this.getClass().getClassLoader(), file.toURI().toURL());
        } catch (NoSuchMethodException | MalformedURLException | InvocationTargetException | IllegalAccessException e) {
            e.printStackTrace();
        }
        this.loadedDependencies.put(dependency, file);
    }


    public File getLoadedDependencyFile(final Dependency dependency) {
        return this.loadedDependencies.get(dependency);
    }

}
