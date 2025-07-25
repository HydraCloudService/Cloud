package de.hydracloud.api.network.packet.service;

import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.NetworkBuf;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

@AllArgsConstructor
@Getter
@NoArgsConstructor
public class ServiceRequestShutdownPacket implements Packet {

    private String service;

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.service = byteBuf.readString();
    }

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        byteBuf.writeString(this.service);
    }

}
