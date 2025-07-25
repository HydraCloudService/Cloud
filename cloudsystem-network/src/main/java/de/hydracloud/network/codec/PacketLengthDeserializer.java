package de.hydracloud.network.codec;

import io.netty.buffer.ByteBuf;
import io.netty.buffer.Unpooled;
import io.netty.channel.ChannelHandlerContext;
import io.netty.handler.codec.ByteToMessageDecoder;

import java.util.List;
import java.util.Objects;

public class PacketLengthDeserializer extends ByteToMessageDecoder {

    private static final int MAX_VARINT_SIZE = 5;

    @Override
    protected void decode(ChannelHandlerContext ctx, ByteBuf in, List<Object> out) {
        Objects.requireNonNull(ctx, "ChannelHandlerContext cannot be null");
        Objects.requireNonNull(in, "Input ByteBuf cannot be null");
        Objects.requireNonNull(out, "Output list cannot be null");

        try {
            if (!ctx.channel().isActive()) {
                in.skipBytes(in.readableBytes());
                return;
            }
            if (!in.isReadable()) {
                return;
            }

            int initialReaderIndex = in.readerIndex();
            byte[] varIntBuffer = new byte[MAX_VARINT_SIZE];

            for (int i = 0; i < MAX_VARINT_SIZE; i++) {
                if (!in.isReadable()) {
                    in.readerIndex(initialReaderIndex);
                    return;
                }

                varIntBuffer[i] = in.readByte();
                if (varIntBuffer[i] >= 0) {
                    ByteBuf varIntBuf = Unpooled.wrappedBuffer(varIntBuffer);

                    try {
                        int packetLength = readVarIntUnchecked(varIntBuf);

                        if (in.readableBytes() < packetLength) {
                            in.readerIndex(initialReaderIndex);
                            return;
                        }

                        out.add(in.readBytes(packetLength));
                    } finally {
                        varIntBuf.release();
                    }
                    return;
                }
            }
        } catch (Exception e) {
            System.err.println("Error decoding packet: " + e.getMessage());
            e.printStackTrace();
        }
    }

    private int readVarIntUnchecked(ByteBuf buf) {
        Objects.requireNonNull(buf, "ByteBuf cannot be null");

        int value = 0;
        int shift = 0;

        for (int i = 0; i < MAX_VARINT_SIZE; i++) {
            if (!buf.isReadable()) {
                break;
            }

            byte currentByte = buf.readByte();
            value |= (currentByte & 0x7F) << shift;
            if ((currentByte & 0x80) == 0) {
                return value;
            }
            shift += 7;
        }

        return value;
    }
}
