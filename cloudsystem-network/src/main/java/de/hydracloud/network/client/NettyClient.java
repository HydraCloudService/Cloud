package de.hydracloud.network.client;

import de.hydracloud.network.packet.Packet;
import de.hydracloud.network.packet.PacketHandler;
import de.hydracloud.network.NetworkType;
import de.hydracloud.network.Node;
import io.netty.bootstrap.Bootstrap;
import io.netty.channel.*;
import org.jetbrains.annotations.NotNull;

import java.util.Objects;

public class NettyClient extends Node {

    private EventLoopGroup eventLoopGroup;
    private Channel channel;

    public NettyClient(@NotNull PacketHandler packetHandler, @NotNull String name, @NotNull NetworkType networkType) {
        super(Objects.requireNonNull(packetHandler, "PacketHandler cannot be null"),
            Objects.requireNonNull(name, "Name cannot be null"),
            Objects.requireNonNull(networkType, "NetworkType cannot be null"));
    }

    @Override
    public void connect(@NotNull String host, int port) {
        Objects.requireNonNull(host, "Host cannot be null");

        try {
            this.eventLoopGroup = this.newEventLoopGroup();

            this.channel = new Bootstrap()
                .channel(this.getSocketChannel())
                .group(this.eventLoopGroup)
                .handler(new NettyClientInitializer(this))
                .option(ChannelOption.SO_KEEPALIVE, true)
                .option(ChannelOption.TCP_NODELAY, true)
                .option(ChannelOption.AUTO_READ, true)
                .connect(host, port)
                .syncUninterruptibly()
                .channel();

            System.out.println("Successfully connected to " + host + ":" + port);
        } catch (Exception e) {
            System.err.println("Failed to connect to " + host + ":" + port + " - " + e.getMessage());
            if (this.eventLoopGroup != null) {
                this.eventLoopGroup.shutdownGracefully();
            }
            throw new RuntimeException("Connection attempt failed", e);
        }
    }

    @Override
    public void close() {
        try {
            if (this.channel != null && this.channel.isActive()) {
                this.channel.close().syncUninterruptibly();
            }
        } catch (Exception e) {
            System.err.println("Error while closing the channel: " + e.getMessage());
        } finally {
            if (this.eventLoopGroup != null) {
                this.eventLoopGroup.shutdownGracefully();
            }
        }
    }

    public void sendPacket(@NotNull Packet packet) {
        Objects.requireNonNull(packet, "Packet cannot be null");

        if (this.channel == null || !this.channel.isActive()) {
            throw new IllegalStateException("Cannot send packet - Channel is not active.");
        }

        this.channel.writeAndFlush(packet).addListener((ChannelFutureListener) future -> {
            if (!future.isSuccess()) {
                System.err.println("Failed to send packet: " + future.cause().getMessage());
            }
        });
    }

    public void onActivated(final ChannelHandlerContext channelHandlerContext) {}

    public void onClose(final ChannelHandlerContext channelHandlerContext) {}
}
