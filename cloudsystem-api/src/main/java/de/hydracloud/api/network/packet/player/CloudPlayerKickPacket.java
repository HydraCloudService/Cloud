package de.hydracloud.api.network.packet.player;

import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

import java.util.UUID;

@Getter
@AllArgsConstructor
@NoArgsConstructor
public class CloudPlayerKickPacket implements Packet {

    private UUID uuid;
    private String proxyService;
    private String reason;

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        byteBuf
            .writeUUID(this.uuid)
            .writeString(this.proxyService)
            .writeString(this.reason);
    }

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.uuid = byteBuf.readUUID();
        this.proxyService = byteBuf.readString();
        this.reason = byteBuf.readString();
    }

}
