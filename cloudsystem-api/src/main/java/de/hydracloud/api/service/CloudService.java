package de.hydracloud.api.service;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.api.service.impl.SimpleService;
import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import org.jetbrains.annotations.NotNull;

import java.util.HashMap;
import java.util.Map;
import java.util.function.Consumer;

public interface CloudService {
    /**
     * @return the name of the service
     */
    @NotNull String getName();

    /**
     * @return the service id
     */
    int getServiceId();

    /**
     * @return the port of the service
     */
    int getPort();

    /**
     * @return the port of the service
     */
    int getPortV6();

    /**
     * @return the host name of the service
     */
    @NotNull String getHostName();

    /**
     * @return the node on which the service is started
     */
    @NotNull String getNode();

    /**
     * @return the group of the service
     */
    @NotNull ServiceGroup getGroup();

    /**
     * sets the state
     *
     * @param state the state to set
     */
    void setState(@NotNull String state);

    /**
     * @return the state of the service
     */
    @NotNull String getState();

    /**
     * @return the extra data properties
     */
    @NotNull HashMap<Object, Object> getExtraData();

    /**
     * sets the state
     *
     * @param extraData the extraData to set
     */
    void setExtraData(@NotNull HashMap<Object, Object> extraData);

    /**
     * @return the max players of the service
     */
    int getMaxPlayers();

    /**
     * sets the max players of the service
     *
     * @param slots the amount to set
     */
    void setMaxPlayers(int slots);

    /**
     * @return the online amount of the service
     */
    default int getOnlineCount() {
        return (int) CloudAPI.getInstance().getPlayerManager().getPlayers()
                .stream()
                .filter(player -> {
                    CloudService service = this.getGroup().getGameServerVersion().isProxy() ? player.getProxyServer() : player.getServer();
                    return service != null && service.equals(this);
                }).count();
    }

    /**
     * @return if the service is full
     */
    default boolean isFull() {
        return this.getOnlineCount() >= this.getMaxPlayers();
    }

    /**
     * edits the properties of the service and update then
     *
     * @param serviceConsumer the consumer to change the properties
     */
    void edit(@NotNull Consumer<CloudService> serviceConsumer);

    /**
     * @return the motd of the service
     */
    @NotNull String getMotd();

    /**
     * sets the motd of the service
     *
     * @param motd the motd to set
     */
    void setMotd(@NotNull String motd);

    /**
     * sends a packet to a service
     *
     * @param packet the packet to send
     */
    void sendPacket(@NotNull Packet packet);

    /**
     * executes a command on the service
     */
    void executeCommand(@NotNull String command);

    /**
     * stops the service
     */
    void stop();

    /**
     * updates the properties of the service
     */
    void update();

    /**
     * writes the service to a network buf
     */
    default void write(@NotNull NetworkBuf networkBuf) {
        if (!this.getGroup().getGameServerVersion().isUseV6()) {
            networkBuf
                    .writeString(this.getGroup().getName())
                    .writeInt(this.getServiceId())
                    .writeString(this.getNode())
                    .writeInt(this.getPort())
                    .writeString(this.getHostName())
                    .writeInt(this.getMaxPlayers())
                    .writeString(this.getState())
                    .writeString(this.getMotd())
                    .writeMap(this.getExtraData());
        } else {
            networkBuf
                    .writeString(this.getGroup().getName())
                    .writeInt(this.getServiceId())
                    .writeString(this.getNode())
                    .writeInt(this.getPort())
                    .writeInt(this.getPortV6())
                    .writeString(this.getHostName())
                    .writeInt(this.getMaxPlayers())
                    .writeString(this.getState())
                    .writeString(this.getMotd())
                    .writeMap(this.getExtraData());
        }
    }

    static CloudService read(@NotNull NetworkBuf buf, @NotNull ServiceGroup group, boolean useV6) {
        int serviceId = buf.readInt();
        String node = buf.readString();
        int port = buf.readInt();

        if (!useV6) {
            String host = buf.readString();
            int maxPlayers = buf.readInt();
            String state = buf.readString();
            String motd = buf.readString();
            HashMap<Object, Object> extraData = buf.readMap();
            return new SimpleService(
                    group.getName(), serviceId, node, port, host, maxPlayers, state, motd, extraData
            );
        } else {
            int portV6 = buf.readInt();
            String host = buf.readString();
            int maxPlayers = buf.readInt();
            String state = buf.readString();
            String motd = buf.readString();
            HashMap<Object, Object> extraData = buf.readMap();
            return new SimpleService(
                    group.getName(), serviceId, node, port, portV6, host, maxPlayers, state, motd, extraData
            );
        }
    }
}
