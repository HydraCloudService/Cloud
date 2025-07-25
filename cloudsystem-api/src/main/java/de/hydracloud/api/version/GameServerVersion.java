package de.hydracloud.api.version;

import lombok.Getter;
import org.jetbrains.annotations.NotNull;

import java.util.HashMap;
import java.util.Map;
import java.util.Objects;

@Getter
public final class GameServerVersion {

    public static final Map<String, GameServerVersion> VERSIONS = new HashMap<>();

    public static final GameServerVersion WATERDOG = new GameServerVersion(
        "waterdog", "latest", true, "https://github.com/WaterdogPE/WaterdogPE/releases/download/latest/Waterdog.jar", false
    );

    public static final GameServerVersion NUKKIT = new GameServerVersion(
        "nukkit", "latest", false, "https://repo.opencollab.dev/api/maven/latest/file/maven-snapshots/cn/nukkit/nukkit/1.0-SNAPSHOT?extension=jar", false
    );

    public static final GameServerVersion ALLAY = new GameServerVersion(
            "allay", "latest", false, "https://github.com/AllayMC/Allay/releases/download/nightly/allay-server-0.7.0-dev-b3269e9-shaded.jar", true
    );

    private final String url;
    private final String title;
    private final String version;
    @Getter
    private final boolean proxy;
    private final boolean useV6;

    private GameServerVersion(final @NotNull String title, final @NotNull String version, final boolean proxy, final @NotNull String url, final boolean useV6) {
        this.url = url;
        this.title = title;
        this.version = version;
        this.proxy = proxy;
        this.useV6 = useV6;
        VERSIONS.put(this.getName(), this);
    }

    public static GameServerVersion getVersionByName(final @NotNull String value) {
        return VERSIONS.get(value);
    }

    public @NotNull String getName() {
        return String.format("%s%s", this.title, !Objects.equals(this.version, "latest") ? "-" + this.version : "");
    }

    public String getJar() {
        return String.format("%s%s.jar", this.title, !Objects.equals(this.version, "latest") ? "-" + this.version : "");
    }
}
