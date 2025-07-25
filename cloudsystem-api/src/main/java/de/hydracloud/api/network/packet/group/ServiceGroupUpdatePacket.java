package de.hydracloud.api.network.packet.group;

import de.hydracloud.api.version.GameServerVersion;
import de.hydracloud.api.groups.ServiceGroup;
import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.NetworkBuf;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

@Getter
@NoArgsConstructor
public final class ServiceGroupUpdatePacket implements Packet {

    private String name;
    private String node;
    private String template;
    private String motd;
    private int memory;
    private int minOnlineService;
    private int maxOnlineService;
    private int defaultMaxPlayers;
    private double startNewPercentage;
    private GameServerVersion gameServerVersion;
    private boolean fallback;
    private boolean maintenance;

    public ServiceGroupUpdatePacket(final ServiceGroup serviceGroup) {
        this.name = serviceGroup.getName();
        this.node = serviceGroup.getNode();
        this.template = serviceGroup.getTemplate();
        this.motd = serviceGroup.getMotd();
        this.memory = serviceGroup.getMaxMemory();
        this.minOnlineService = serviceGroup.getMinOnlineService();
        this.maxOnlineService = serviceGroup.getMaxOnlineService();
        this.defaultMaxPlayers = serviceGroup.getDefaultMaxPlayers();
        this.gameServerVersion = serviceGroup.getGameServerVersion();
        this.startNewPercentage = serviceGroup.getStartNewPercentage();
        this.fallback = serviceGroup.isFallbackGroup();
        this.maintenance = serviceGroup.isMaintenance();
    }

    @Override
    public void read(final @NotNull NetworkBuf byteBuf) {
        this.name = byteBuf.readString();
        this.node = byteBuf.readString();
        this.template = byteBuf.readString();
        this.motd = byteBuf.readString();
        this.memory = byteBuf.readInt();
        this.minOnlineService = byteBuf.readInt();
        this.maxOnlineService = byteBuf.readInt();
        this.defaultMaxPlayers = byteBuf.readInt();
        this.startNewPercentage = byteBuf.readDouble();
        this.gameServerVersion = GameServerVersion.getVersionByName(byteBuf.readString());
        this.fallback = byteBuf.readBoolean();
        this.maintenance = byteBuf.readBoolean();
    }

    @Override
    public void write(final @NotNull NetworkBuf byteBuf) {
        byteBuf
            .writeString(this.name)
            .writeString(this.node)
            .writeString(this.template)
            .writeString(this.motd)
            .writeInt(this.memory)
            .writeInt(this.minOnlineService)
            .writeInt(this.maxOnlineService)
            .writeInt(this.defaultMaxPlayers)
            .writeDouble(this.startNewPercentage)
            .writeString(this.gameServerVersion.getName())
            .writeBoolean(this.fallback)
            .writeBoolean(this.maintenance);
    }

}
