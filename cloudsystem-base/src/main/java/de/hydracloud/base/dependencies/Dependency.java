package de.hydracloud.base.dependencies;

import lombok.Getter;

@Getter
public enum Dependency {
    GSON("com.google.code.gson", "gson"),
    JLINE("org.jline", "jline"),
    JANSI("org.fusesource.jansi", "jansi"),
    COMMONS_IO("commons-io", "commons-io"),
    NETTY_TRANSPORT("io.netty", "netty-transport"),
    NETTY_COMMON("io.netty", "netty-common"),
    NETTY_BUFFER("io.netty", "netty-buffer"),
    NETTY_RESOLVER("io.netty", "netty-resolver"),
    NETTY_TRANSPORT_EPOLL("io.netty", "netty-transport-classes-epoll"),
    NETTY_UNIX_COMMON("io.netty", "netty-transport-native-unix-common"),
    NETTY_CODEC("io.netty", "netty-codec"),
    MYSQL_DRIVER("mysql", "mysql-connector-java"),
    MONGO_DRIVER("org.mongodb", "mongo-java-driver"),
    H2_DATABASE("com.h2database", "h2");

    private static final String MAVEN_FORMAT = "%s/%s/%s/%s-%s.jar";

    private final String groupId;
    private final String artifactId;

    Dependency(final String groupId, final String artifactId) {
        this.groupId = groupId;
        this.artifactId = artifactId;
    }

    public String getFileName(String version) {
        return this.name().toLowerCase().replace('_', '-') + "-" + version + ".jar";
    }

    public String getMavenRepoPath(String version) {
        return MAVEN_FORMAT.formatted(
            groupId.replace('.', '/'),
            artifactId,
            version,
            artifactId,
            version
        );
    }
}
