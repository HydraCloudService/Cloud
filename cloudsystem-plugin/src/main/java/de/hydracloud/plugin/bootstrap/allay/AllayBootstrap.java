package de.hydracloud.plugin.bootstrap.allay;

import de.hydracloud.api.bootstrap.allay.AllayCloudBootstrap;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.plugin.bootstrap.allay.commands.CloudPlayerInfoCommand;
import de.hydracloud.plugin.bootstrap.allay.listener.AllayListener;
import de.hydracloud.wrapper.Wrapper;
import org.allaymc.api.plugin.Plugin;
import org.allaymc.api.registry.Registries;
import org.allaymc.api.server.Server;

public class AllayBootstrap extends Plugin {

    @Override
    public void onEnable() {
        // update that the service is ready to use
        final var service = Wrapper.getInstance().thisService();

        if (service.getGroup().isAutoUpdating()) {
            service.setState(ServiceState.ONLINE);
            service.update();
        }

        new AllayCloudBootstrap(Server.getInstance());

        Registries.COMMANDS.register(new CloudPlayerInfoCommand());
        Server.getInstance().getEventBus().registerListener(new AllayListener());
    }
}