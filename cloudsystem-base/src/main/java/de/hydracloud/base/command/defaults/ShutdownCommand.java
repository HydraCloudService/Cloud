package de.hydracloud.base.command.defaults;

import de.hydracloud.base.Base;
import de.hydracloud.base.command.CloudCommand;

@CloudCommand.Command(name = "stop", description = "Stops the cloudsystem", aliases = "exit")
public final class ShutdownCommand extends CloudCommand {

    @Override
    public void execute(Base base, String[] args) {
        base.onShutdown();
    }

}
