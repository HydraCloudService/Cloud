package de.hydracloud.network.codec;

import io.netty.buffer.ByteBuf;
import io.netty.channel.ChannelHandlerContext;
import io.netty.handler.codec.MessageToByteEncoder;
import java.util.Objects;

public class PacketLengthSerializer extends MessageToByteEncoder<ByteBuf> {

    @Override
    protected void encode(ChannelHandlerContext ctx, ByteBuf in, ByteBuf out) {
        Objects.requireNonNull(ctx, "ChannelHandlerContext cannot be null");
        Objects.requireNonNull(in, "Input ByteBuf cannot be null");
        Objects.requireNonNull(out, "Output ByteBuf cannot be null");

        try {
            int readable = in.readableBytes();
            out.ensureWritable(readable + getVarIntSize(readable));
            writeVarInt(out, readable);
            out.writeBytes(in, in.readerIndex(), readable);
        } catch (Exception e) {
            System.err.println("Failed to encode packet length: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private void writeVarInt(ByteBuf buf, int value) {
        Objects.requireNonNull(buf, "ByteBuf cannot be null");

        while (true) {
            if ((value & -128) == 0) {
                buf.writeByte(value);
                return;
            }
            buf.writeByte((value & 127) | 128);
            value >>>= 7;
        }
    }

    private int getVarIntSize(int value) {
        return switch (Integer.numberOfLeadingZeros(value)) {
            case 25, 26, 27, 28, 29, 30, 31 -> 1;
            case 18, 19, 20, 21, 22, 23, 24 -> 2;
            case 11, 12, 13, 14, 15, 16, 17 -> 3;
            case 4, 5, 6, 7, 8, 9, 10 -> 4;
            default -> 5;
        };
    }
}
