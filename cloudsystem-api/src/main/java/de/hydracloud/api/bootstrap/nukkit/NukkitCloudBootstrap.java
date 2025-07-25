package de.hydracloud.api.bootstrap.nukkit;

import cn.nukkit.Server;
import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.network.packet.service.ServiceDispatchCommandPacket;

public class NukkitCloudBootstrap {

    public NukkitCloudBootstrap(Server server) {
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(ServiceDispatchCommandPacket.class, (channelHandlerContext, packet) -> {
            var command = packet.getCommand();
            server.dispatchCommand(server.getConsoleSender(), command);
        });
    }
}
