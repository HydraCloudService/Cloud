package de.hydracloud.network.server.client;

import de.hydracloud.network.NetworkType;
import de.hydracloud.network.packet.Packet;
import io.netty.channel.Channel;
import org.jetbrains.annotations.NotNull;

public record ConnectedClient(String name, Channel channel, NetworkType networkType) {

    public void sendPacket(@NotNull Packet packet) {
        this.channel.writeAndFlush(packet);
    }

    public void sendPackets(@NotNull Packet... packets) {
        for (final var packet : packets) this.channel.write(packet);
        this.channel.flush();
    }
}
