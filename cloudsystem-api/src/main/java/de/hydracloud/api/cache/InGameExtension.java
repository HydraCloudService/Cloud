package de.hydracloud.api.cache;

import java.util.HashMap;
import java.util.Map;

public class InGameExtension {
    public static final String SIGN_EXTENSION = "sign_extension";
    public static final String NPC_EXTENSION = "npc_extension";
    public static final String HUB_COMMAND_EXTENSION = "hub_command_extension";
    public static final String NOTIFY_EXTENSION = "notify_extension";

    private static final Map<String, Boolean> moduleStates = new HashMap<>();

    static {
        moduleStates.put(SIGN_EXTENSION, false);
        moduleStates.put(NPC_EXTENSION, false);
        moduleStates.put(HUB_COMMAND_EXTENSION, false);
        moduleStates.put(NOTIFY_EXTENSION, false);
    }

    public static void setModuleState(String module, boolean enabled) {
        if (moduleStates.containsKey(module)) {
            moduleStates.put(module, enabled);
        }
    }

    public static boolean getModuleState(String module) {
        return moduleStates.getOrDefault(module, false);
    }

    public static String[] getAll() {
        return new String[]{SIGN_EXTENSION, NPC_EXTENSION, HUB_COMMAND_EXTENSION};
    }
}
