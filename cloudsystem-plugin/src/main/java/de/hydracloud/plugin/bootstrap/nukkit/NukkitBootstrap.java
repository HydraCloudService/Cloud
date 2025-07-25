package de.hydracloud.plugin.bootstrap.nukkit;

import cn.nukkit.Server;
import cn.nukkit.plugin.PluginBase;
import de.hydracloud.api.bootstrap.nukkit.NukkitCloudBootstrap;
import de.hydracloud.api.cache.InGameExtension;
import de.hydracloud.api.service.ServiceState;
import de.hydracloud.plugin.bootstrap.nukkit.commands.CloudPlayerInfoCommand;
import de.hydracloud.plugin.bootstrap.nukkit.extensions.signs.CloudSignsExtension;
import de.hydracloud.plugin.bootstrap.nukkit.listener.NukkitListener;
import de.hydracloud.wrapper.Wrapper;
import de.hydracloud.plugin.bootstrap.nukkit.commands.CloudTransferCommand;

public final class NukkitBootstrap extends PluginBase {

    @Override
    public void onEnable() {
        // update that the service is ready to use
        final var service = Wrapper.getInstance().thisService();

        if (service.getGroup().isAutoUpdating()) {
            service.setState(ServiceState.ONLINE);
            service.update();
        }

        new NukkitCloudBootstrap(this.getServer());

        Server.getInstance().getCommandMap().register("transfer", new CloudTransferCommand());
        Server.getInstance().getCommandMap().register("playerinfo", new CloudPlayerInfoCommand());
        Server.getInstance().getPluginManager().registerEvents(new NukkitListener(), this);

        if (InGameExtension.getModuleState(InGameExtension.SIGN_EXTENSION) && service.getGroup().isFallbackGroup()) (new CloudSignsExtension()).load(this);
        if (InGameExtension.getModuleState(InGameExtension.NPC_EXTENSION)) {
            //ToDo: implement CloudNPCs
        }
    }
}
