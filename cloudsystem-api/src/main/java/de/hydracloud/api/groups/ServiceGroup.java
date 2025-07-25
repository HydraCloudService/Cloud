package de.hydracloud.api.groups;

import de.hydracloud.api.groups.impl.SimpleServiceGroup;
import de.hydracloud.api.version.GameServerVersion;
import de.hydracloud.network.packet.NetworkBuf;
import org.jetbrains.annotations.NotNull;

import java.util.function.Consumer;

public interface ServiceGroup {

    /**
     * @return the name of the group
     */
    @NotNull String getName();

    /**
     * @return the template of the group
     */
    @NotNull String getTemplate();

    /**
     * sets the template of the group
     *
     * @param template the template to set
     */
    void setTemplate(@NotNull String template);

    /**
     * @return the node of the group
     */
    @NotNull String getNode();

    /**
     * sets the node of the group
     *
     * @param node the node to set
     */
    void setNode(@NotNull String node);

    /**
     * @return the max memory of a service of the group
     */
    int getMaxMemory();

    /**
     * sets the max memory of a service of the group
     *
     * @param memory the memory to set
     */
    void setMaxMemory(int memory);

    /**
     * @return the max players of a service of the group
     */
    int getDefaultMaxPlayers();

    /**
     * sets the max players of a service of the group
     *
     * @param defaultMaxPlayers the max players to set
     */
    void setDefaultMaxPlayers(int defaultMaxPlayers);

    /**
     * @return the minimum online services of the group
     */
    int getMinOnlineService();

    /**
     * sets the minimum online services of the group
     *
     * @param minOnlineService the amount to set
     */
    void setMinOnlineService(int minOnlineService);

    /**
     * @return the maximum online services of the group
     */
    int getMaxOnlineService();

    /**
     * sets the maximum online services of the group
     *
     * @param maxOnlineService the amount to set
     */
    void setMaxOnlineService(int maxOnlineService);

    /**
     * @return the percentage of online players to start a new service
     */
    double getStartNewPercentage();

    /**
     * sets the percentage of online players to start a new service
     *
     * @param startNewPercentage the amount to set
     */
    void setStartNewPercentage(double startNewPercentage);

    /**
     * @return the maintenance state
     */
    boolean isMaintenance();

    /**
     * sets the maintenance of the group
     *
     * @param maintenance the amount to set
     */
    void setMaintenance(boolean maintenance);

    /**
     * @return if the group is static or not
     */
    boolean isStatic();

    /**
     * @return if the group is a fallback group
     */
    boolean isFallbackGroup();

    /**
     * sets if the group is a fallback group
     *
     * @param fallbackGroup the value to set
     */
    void setFallbackGroup(boolean fallbackGroup);

    /**
     * @return the game server version of the group
     */
    @NotNull GameServerVersion getGameServerVersion();

    /**
     * sets the game server version of the group
     *
     * @param gameServerVersion the game server version to set
     */
    void setGameServerVersion(@NotNull GameServerVersion gameServerVersion);

    /**
     * @return the default motd of a service of the group
     */
    @NotNull String getMotd();

    /**
     * sets the default motd of a service of the group
     *
     * @param motd the motd to set as default
     */
    void setMotd(@NotNull String motd);

    /**
     * edits the properties of the group and update then
     *
     * @param serviceGroupConsumer the consumer to change the properties
     */
    void edit(@NotNull Consumer<ServiceGroup> serviceGroupConsumer);

    /**
     * get auto update state
     *
     * @return true if the group can auto-able update
     */
    boolean isAutoUpdating();

    /**
     * updates the properties of the group
     */
    void update();

    /**
     * writes the group to a network buf
     */
    default void write(@NotNull NetworkBuf networkBuf) {
        networkBuf
            .writeString(this.getName())
            .writeString(this.getTemplate())
            .writeString(this.getNode())
            .writeString(this.getMotd())
            .writeInt(this.getMaxMemory())
            .writeInt(this.getDefaultMaxPlayers())
            .writeInt(this.getMinOnlineService())
            .writeInt(this.getMaxOnlineService())
            .writeDouble(this.getStartNewPercentage())
            .writeBoolean(this.isStatic())
            .writeBoolean(this.isFallbackGroup())
            .writeBoolean(this.isMaintenance())
            .writeBoolean(this.isAutoUpdating())
            .writeString(this.getGameServerVersion().getName());
    }

    /**
     * reads a group from a network buf
     */
    static ServiceGroup read(@NotNull NetworkBuf networkBuf) {
        return new SimpleServiceGroup(
            networkBuf.readString(),
            networkBuf.readString(),
            networkBuf.readString(),
            networkBuf.readString(),
            networkBuf.readInt(),
            networkBuf.readInt(),
            networkBuf.readInt(),
            networkBuf.readInt(),
            networkBuf.readDouble(),
            networkBuf.readBoolean(),
            networkBuf.readBoolean(),
            networkBuf.readBoolean(),
            networkBuf.readBoolean(),
            GameServerVersion.getVersionByName(networkBuf.readString()));
    }

}
