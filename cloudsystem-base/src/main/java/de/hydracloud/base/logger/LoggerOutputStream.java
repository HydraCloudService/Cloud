package de.hydracloud.base.logger;

import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.logger.Logger;

import java.io.ByteArrayOutputStream;
import java.nio.charset.StandardCharsets;

public final class LoggerOutputStream extends ByteArrayOutputStream {

    private final Logger logger;
    private final LogType logType;

    public LoggerOutputStream(Logger logger, LogType logType) {
        this.logger = logger;
        this.logType = logType;
    }

    @Override
    public void flush() {
        String input = toString(StandardCharsets.UTF_8);
        reset();

        if (input == null || input.trim().isEmpty()) {
            return;
        }

        String sanitizedInput = input.replaceAll("\\p{Cntrl}&&[^\n" +
                "]", "").trim();

        String[] lines = sanitizedInput.split("\\r?\\n");
        for (String line : lines) {
            if (!line.trim().isEmpty()) {
                logger.log(line, logType);
            }
        }
    }
}
