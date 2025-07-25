package de.hydracloud.api.bootstrap.allay;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.network.packet.service.ServiceDispatchCommandPacket;
import org.allaymc.api.registry.Registries;
import org.allaymc.api.server.Server;

public class AllayCloudBootstrap {

    public AllayCloudBootstrap(Server server) {
        CloudAPI.getInstance().getPacketHandler().registerPacketListener(ServiceDispatchCommandPacket.class, (channelHandlerContext, packet) -> {
            var command = packet.getCommand();
            Registries.COMMANDS.execute(server, command);
        });
    }
}
