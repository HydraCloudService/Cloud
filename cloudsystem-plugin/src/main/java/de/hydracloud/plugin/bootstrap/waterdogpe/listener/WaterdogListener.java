package de.hydracloud.plugin.bootstrap.waterdogpe.listener;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.event.player.CloudPlayerUpdateEvent;
import de.hydracloud.api.player.PlayerManager;
import de.hydracloud.api.player.impl.SimpleCloudPlayer;
import de.hydracloud.api.service.ServiceManager;
import de.hydracloud.plugin.bootstrap.waterdogpe.WaterdogBootstrap;
import de.hydracloud.wrapper.Wrapper;
import de.hydracloud.wrapper.service.WrapperServiceManager;
import dev.waterdog.waterdogpe.event.defaults.*;
import org.jetbrains.annotations.NotNull;

import java.util.Objects;

public final class WaterdogListener {

    private final WaterdogBootstrap bootstrap;

    private final PlayerManager playerManager;
    private final ServiceManager serviceManager;

    public WaterdogListener(final WaterdogBootstrap bootstrap) {
        this.bootstrap = bootstrap;
        this.playerManager = CloudAPI.getInstance().getPlayerManager();
        this.serviceManager = CloudAPI.getInstance().getServiceManager();
    }

    public void handle(final PlayerLoginEvent event) {
        if (playerManager.getOnlineCount() >= Wrapper.getInstance().thisService().getMaxPlayers()) {
            event.setCancelReason("§cThis network has reached the maximum number of players.");
            event.setCancelled(true);
            return;
        }

        final var connection = event.getPlayer();
        this.playerManager.registerCloudPlayer(new SimpleCloudPlayer(connection.getAddress().getAddress().getHostAddress(), connection.getUniqueId(), connection.getName(),
            ((WrapperServiceManager) serviceManager).thisService()));
    }

    public void handle(final @NotNull ServerTransferRequestEvent event) {
        playerManager.getCloudPlayer(event.getPlayer().getUniqueId())
            .ifPresent(cloudPlayer -> {
                cloudPlayer.setServer(Objects.requireNonNull(CloudAPI.getInstance().getServiceManager()
                    .getServiceByNameOrNull(event.getTargetServer().getServerName())));
                cloudPlayer.update(CloudPlayerUpdateEvent.UpdateReason.SERVER_SWITCH);
            });
    }

    public void handle(@NotNull PlayerDisconnectedEvent event) {
        this.playerManager.unregisterCloudPlayer(event.getPlayer().getUniqueId());
    }

    public void handle(@NotNull ProxyPingEvent event) {
        final var players = event.getPlayers();

        event.setMaximumPlayerCount(Wrapper.getInstance().thisService().getMaxPlayers());
        event.setPlayerCount(this.playerManager.getOnlineCount());
        event.setMotd(Wrapper.getInstance().thisService().getMotd());
    }

    public void handle(final @NotNull InitialServerDeterminedEvent event) {
        this.bootstrap.getFallback(event.getPlayer())
            .ifPresent(service -> {
                //event.setCancelled(true);
            });
    }

}
