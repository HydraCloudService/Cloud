package de.hydracloud.plugin.bootstrap.waterdogpe.extensions.hub;

import de.hydracloud.plugin.bootstrap.waterdogpe.WaterdogBootstrap;
import de.hydracloud.plugin.bootstrap.waterdogpe.extensions.hub.command.HubCommand;
import dev.waterdog.waterdogpe.ProxyServer;
import lombok.Getter;

public class HubExtension {
    @Getter private static HubExtension instance;
    @Getter private WaterdogBootstrap bootstrap;

    public HubExtension(WaterdogBootstrap bootstrap) {
        instance = this;
        this.bootstrap = bootstrap;

        ProxyServer.getInstance().getCommandMap().registerCommand(new HubCommand());
    }
}
