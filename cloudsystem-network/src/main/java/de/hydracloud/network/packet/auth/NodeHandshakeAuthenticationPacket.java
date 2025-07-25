package de.hydracloud.network.packet.auth;

import de.hydracloud.network.NetworkType;
import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import lombok.Getter;
import org.jetbrains.annotations.NotNull;

@Getter
public final class NodeHandshakeAuthenticationPacket implements Packet {

    private String name;
    private NetworkType type;

    public NodeHandshakeAuthenticationPacket() {
    }

    public NodeHandshakeAuthenticationPacket(final String name, final NetworkType type) {
        this.name = name;
        this.type = type;
    }

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.type = byteBuf.readEnum();
        this.name = byteBuf.readString();
    }

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        byteBuf
            .writeEnum(this.type)
            .writeString(this.name);
    }

}
