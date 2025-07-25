package de.hydracloud.api.extensions;

import lombok.Getter;
import lombok.Setter;

import java.util.HashMap;
import java.util.Map;

@Getter
@Setter
public class ExtenstionConfiguartion {
    private Map<String, Boolean> extensions = new HashMap<>();

    public ExtenstionConfiguartion() {
        extensions.put("sign_extension", false);
        extensions.put("npc_extension", false);
        extensions.put("hub_command_extension", false);
        extensions.put("notify_extension", false);
    }
}