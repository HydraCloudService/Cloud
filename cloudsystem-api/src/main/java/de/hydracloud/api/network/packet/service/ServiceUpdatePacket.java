package de.hydracloud.api.network.packet.service;

import de.hydracloud.api.service.CloudService;
import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

import java.util.HashMap;

@NoArgsConstructor
@Getter
public class ServiceUpdatePacket implements Packet {

    private String service;
    private String state;

    private int maxPlayers;
    private String motd;

    private HashMap<Object, Object> extraData;

    public ServiceUpdatePacket(CloudService service) {
        this.service = service.getName();
        this.state = service.getState();
        this.maxPlayers = service.getMaxPlayers();
        this.motd = service.getMotd();
        this.extraData = service.getExtraData();
    }

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        byteBuf
                .writeString(this.service)
                .writeString(this.state)
                .writeInt(this.maxPlayers)
                .writeString(this.motd)
                .writeMap(this.extraData);
    }

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.service = byteBuf.readString();
        this.state = byteBuf.readString();
        this.maxPlayers = byteBuf.readInt();
        this.motd = byteBuf.readString();
        this.extraData = byteBuf.readMap();
    }
}
