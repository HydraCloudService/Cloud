package de.hydracloud.api.bootstrap.waterdogpe;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.network.packet.player.CloudPlayerKickPacket;
import de.hydracloud.api.network.packet.player.CloudPlayerMessagePacket;
import de.hydracloud.api.network.packet.player.CloudPlayerSendServicePacket;
import de.hydracloud.api.network.packet.service.ServiceBroadcastMessagePacket;
import de.hydracloud.api.network.packet.service.ServiceDispatchCommandPacket;
import de.hydracloud.api.player.CloudPlayer;
import dev.waterdog.waterdogpe.ProxyServer;

public class WaterdogCloudBootstrap {

    public void load(ProxyServer server) {
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(CloudPlayerKickPacket.class, (channelHandlerContext, packet) -> {
            var player = server.getPlayer(packet.getUuid());
            assert player != null;
            player.disconnect(packet.getReason());
        });
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(CloudPlayerMessagePacket.class, (channelHandlerContext, packet) -> {
            var player = server.getPlayer(packet.getUuid());
            assert player != null;
            player.sendMessage(packet.getMessage());
        });
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(CloudPlayerSendServicePacket.class, (channelHandlerContext, packet) -> {
            var player = server.getPlayer(packet.getUuid());
            assert player != null;
            if (player.getServerInfo() != null && player.getServerInfo().getServerName().equals(packet.getService())) return;
            player.connect(server.getServerInfo(packet.getService()));
        });
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(ServiceBroadcastMessagePacket.class, (channelHandlerContext, packet) -> {
            var message = packet.getMessage();
            assert message != null;
            for (CloudPlayer cloudPlayer : CloudAPI.getInstance().getPlayerManager().getPlayers()) {
                if (cloudPlayer != null) {
                    cloudPlayer.sendMessage(message);
                }
            }
        });
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(ServiceDispatchCommandPacket.class, (channelHandlerContext, packet) -> {
            var command = packet.getCommand();
            server.dispatchCommand(server.getConsoleSender(), command);
        });

        server.getLogger().info("Loaded " + this.getClass().getSimpleName() + " successfully.");
    }
}
