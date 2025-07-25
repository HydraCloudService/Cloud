package de.hydracloud.plugin.bootstrap.waterdogpe.extensions.notify.listener;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.event.service.CloudServiceRegisterEvent;
import de.hydracloud.api.event.service.CloudServiceRemoveEvent;
import de.hydracloud.api.event.service.CloudServiceUpdateEvent;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.plugin.bootstrap.waterdogpe.extensions.notify.utils.Messages;
import dev.waterdog.waterdogpe.ProxyServer;
import dev.waterdog.waterdogpe.player.ProxiedPlayer;

public class NotifyCloudListener {

    public NotifyCloudListener() {
        CloudAPI.getInstance().getEventHandler().registerEvent(CloudServiceRegisterEvent.class, event -> {
            for (ProxiedPlayer player : ProxyServer.getInstance().getPlayers().values()) {
                if (player.hasPermission("cloud.notify")) {
                    player.sendMessage(CloudAPI.getPrefix() + Messages.server_preparing_message.replace("%server%", event.getService().getName()));
                }
            }
        });

        CloudAPI.getInstance().getEventHandler().registerEvent(CloudServiceUpdateEvent.class, event -> {
            if (event.getService().getState().equals(ServiceState.ONLINE)) {
                for (ProxiedPlayer player : ProxyServer.getInstance().getPlayers().values()) {
                    if (player.hasPermission("cloud.notify")) {
                        player.sendMessage(CloudAPI.getPrefix() + Messages.server_start_message.replace("%server%", event.getService().getName()));
                    }
                }
            }
        });

        CloudAPI.getInstance().getEventHandler().registerEvent(CloudServiceRemoveEvent.class, event -> {
            for (ProxiedPlayer player : ProxyServer.getInstance().getPlayers().values()) {
                if (player.hasPermission("cloud.notify")) {
                    player.sendMessage(CloudAPI.getPrefix() + Messages.server_stop_message.replace("%server%", event.getService()));
                }
            }
        });
    }
}
