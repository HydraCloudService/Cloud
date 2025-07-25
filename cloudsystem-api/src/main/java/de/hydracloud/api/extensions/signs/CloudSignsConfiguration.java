package de.hydracloud.api.extensions.signs;

import lombok.Getter;

import java.util.HashMap;
import java.util.Map;

@Getter
public class CloudSignsConfiguration {
    private Map<String, Object> signs = new HashMap<>();
}
