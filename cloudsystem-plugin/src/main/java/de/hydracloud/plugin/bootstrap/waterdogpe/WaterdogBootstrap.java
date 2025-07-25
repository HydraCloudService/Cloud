package de.hydracloud.plugin.bootstrap.waterdogpe;

import de.hydracloud.api.CloudAPI;
import de.hydracloud.api.bootstrap.waterdogpe.WaterdogCloudBootstrap;
import de.hydracloud.api.cache.InGameExtension;
import de.hydracloud.api.service.CloudService;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.plugin.bootstrap.waterdogpe.extensions.hub.HubExtension;
import de.hydracloud.plugin.bootstrap.waterdogpe.extensions.notify.NotifyExtension;
import de.hydracloud.plugin.bootstrap.waterdogpe.handler.JoinHandler;
import de.hydracloud.plugin.bootstrap.waterdogpe.handler.ReconnectHandler;
import de.hydracloud.plugin.bootstrap.waterdogpe.listener.WaterdogCloudListener;
import de.hydracloud.plugin.bootstrap.waterdogpe.listener.WaterdogListener;
import de.hydracloud.plugin.bootstrap.waterdogpe.commands.WaterdogCloudCommand;
import de.hydracloud.wrapper.Wrapper;
import dev.waterdog.waterdogpe.ProxyServer;
import dev.waterdog.waterdogpe.event.defaults.*;
import dev.waterdog.waterdogpe.player.ProxiedPlayer;
import dev.waterdog.waterdogpe.plugin.Plugin;
import lombok.Getter;
import org.jetbrains.annotations.NotNull;

import java.util.Comparator;
import java.util.Optional;

public final class WaterdogBootstrap extends Plugin {
    @Getter
    private WaterdogCloudListener cloudListener;
    @Getter
    private static WaterdogBootstrap instance;

    @Override
    public void onStartup() {
        instance = this;
    }

    @Override
    public void onEnable() {
        WaterdogListener listener = new WaterdogListener(this);

        // update that the service is ready to use
        var service = Wrapper.getInstance().thisService();

        if (service.getGroup().isAutoUpdating()) {
            service.setState(ServiceState.ONLINE);
            service.update();
        }

        var waterdogSyncBootstrap = new WaterdogCloudBootstrap();
        waterdogSyncBootstrap.load(this.getProxy());

        this.cloudListener = new WaterdogCloudListener(Wrapper.getInstance());

        getProxy().getEventManager().subscribe(PlayerLoginEvent.class, listener::handle);
        getProxy().getEventManager().subscribe(ServerTransferRequestEvent.class, listener::handle);
        getProxy().getEventManager().subscribe(PlayerDisconnectedEvent.class, listener::handle);
        getProxy().getEventManager().subscribe(ProxyPingEvent.class, listener::handle);
        getProxy().getEventManager().subscribe(InitialServerDeterminedEvent.class, listener::handle);

        getProxy().getCommandMap().registerCommand(new WaterdogCloudCommand());
        if (InGameExtension.getModuleState(InGameExtension.HUB_COMMAND_EXTENSION)) {
            new HubExtension(this);
        }

        if (InGameExtension.getModuleState(InGameExtension.NOTIFY_EXTENSION)) {
            new NotifyExtension(this);
        }

        ProxyServer.getInstance().setJoinHandler(new JoinHandler(this));
        ProxyServer.getInstance().setReconnectHandler(new ReconnectHandler(this));
    }

    public @NotNull Optional<CloudService> getFallback(final ProxiedPlayer player) {
        return CloudAPI.getInstance().getServiceManager().getAllCachedServices().stream()
            .filter(service -> service.getState().equals(ServiceState.ONLINE))
            .filter(service -> !service.getState().equals(ServiceState.HIDDEN))
            .filter(service -> !service.getState().equals(ServiceState.INGAME))
            .filter(service -> !service.getGroup().getGameServerVersion().isProxy())
            .filter(service -> service.getGroup().isFallbackGroup())
            .filter(service -> (player.getServerInfo() == null || !player.getServerInfo().getServerName().equals(service.getName())))
            .filter(service -> (!service.isFull()))
            .min(Comparator.comparing(CloudService::getOnlineCount));
    }
}
