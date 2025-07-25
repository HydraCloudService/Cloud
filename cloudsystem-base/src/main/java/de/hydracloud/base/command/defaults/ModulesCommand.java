package de.hydracloud.base.command.defaults;

import de.hydracloud.base.Base;
import de.hydracloud.base.command.CloudCommand;
import de.hydracloud.modules.api.ILoadedModule;
import de.hydracloud.modules.api.annotation.Module;

import java.util.List;
import java.util.Map;

@CloudCommand.Command(name = "modules", description = "Modules command")
public class ModulesCommand extends CloudCommand {

    @Override
    public void execute(Base base, String[] args) {
        this.sendModuleList();
    }

    private void sendModuleList() {
        StringBuilder list = new StringBuilder();
        List<ILoadedModule> modules = Base.getInstance().getModuleProvider().getAllModules();
        for (ILoadedModule module : modules) {
            if (!list.isEmpty()) {
                list.append("§f, ");
            }
            list.append("§a");
            list.append(module.getDescription());
        }

        Base.getInstance().getLogger().log("§eCurrently are §f" + modules.size() + " §emodules loaded§f:\n" + list.toString());
    }
}
