package de.hydracloud.base.command.defaults;

import de.hydracloud.base.Base;
import de.hydracloud.base.command.CloudCommand;

@CloudCommand.Command(name = "help", description = "All commands and help description")
public final class HelpCommand extends CloudCommand {

    @Override
    public void execute(Base base, String[] args) {
        final var manager = Base.getInstance().getCommandManager();

        base.getLogger().log("§7All possible commands(§b" + manager.getCachedCloudCommands().size() + "§7):");
        manager.getCachedCloudCommands().values().stream().distinct().forEach(it -> base.getLogger()
            .log("§b" + it.getName() + getAliases(it) + " §7- " + it.getDescription()));
    }

    private String getAliases(CloudCommand command) {
        return command.getAliases().length == 0 ? "" : "§7(§b" + String.join(", ", command.getAliases()) + "§7)";
    }

}
