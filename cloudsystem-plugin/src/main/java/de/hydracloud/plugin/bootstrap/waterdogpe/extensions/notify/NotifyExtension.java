package de.hydracloud.plugin.bootstrap.waterdogpe.extensions.notify;

import de.hydracloud.plugin.bootstrap.waterdogpe.WaterdogBootstrap;
import de.hydracloud.plugin.bootstrap.waterdogpe.extensions.notify.listener.NotifyCloudListener;
import lombok.Getter;

public class NotifyExtension {
    @Getter private static NotifyExtension instance;
    @Getter private WaterdogBootstrap waterdogBootstrap;

    public NotifyExtension(WaterdogBootstrap bootstrap) {
        instance = this;
        waterdogBootstrap = bootstrap;

        new NotifyCloudListener();
    }
}
