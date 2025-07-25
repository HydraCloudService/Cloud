package de.hydracloud.network.codec;

import de.hydracloud.network.packet.NetworkBuf;
import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.PacketHandler;
import io.netty.buffer.ByteBuf;
import io.netty.channel.ChannelHandlerContext;
import io.netty.handler.codec.MessageToByteEncoder;

public final class PacketEncoder extends MessageToByteEncoder<Packet> {

    private final PacketHandler packetHandler;

    public PacketEncoder(final PacketHandler packetHandler) {
        this.packetHandler = packetHandler;
    }

    @Override
    protected void encode(ChannelHandlerContext channelHandlerContext, Packet packet, ByteBuf byteBuf) {
        byteBuf.writeInt(this.packetHandler.getPacketId(packet.getClass()));
        packet.write(new NetworkBuf(byteBuf));
    }

}
