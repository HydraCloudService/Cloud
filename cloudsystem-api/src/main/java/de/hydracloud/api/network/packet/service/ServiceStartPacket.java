package de.hydracloud.api.network.packet.service;

import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import lombok.AllArgsConstructor;
import lombok.Getter;
import lombok.NoArgsConstructor;
import org.jetbrains.annotations.NotNull;

@AllArgsConstructor
@Getter
@NoArgsConstructor
public class ServiceStartPacket implements Packet {

    private String serviceGroup;

    @Override
    public void read(@NotNull NetworkBuf byteBuf) {
        this.serviceGroup = byteBuf.readString();
    }

    @Override
    public void write(@NotNull NetworkBuf byteBuf) {
        byteBuf.writeString(this.serviceGroup);
    }
}
