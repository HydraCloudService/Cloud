package de.hydracloud.base.command.defaults;

import de.hydracloud.base.Base;
import de.hydracloud.base.command.CloudCommand;

@CloudCommand.Command(name = "clear", description = "Clears the console")
public final class ClearCommand extends CloudCommand {

    @Override
    public void execute(Base base, String[] args) {
        base.getConsoleManager().clearConsole();
    }

}
