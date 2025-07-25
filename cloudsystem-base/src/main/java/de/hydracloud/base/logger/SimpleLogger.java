package de.hydracloud.base.logger;

import de.hydracloud.base.console.SimpleConsoleManager;
import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.logger.Logger;
import de.hydracloud.api.logger.LoggerAnsiFactory;
import lombok.Getter;
import lombok.SneakyThrows;
import org.jetbrains.annotations.NotNull;
import org.jline.utils.InfoCmp;

import java.io.PrintStream;
import java.time.LocalTime;
import java.time.format.DateTimeFormatter;
import java.util.Locale;

@Getter
public final class SimpleLogger implements Logger {

    private static final DateTimeFormatter TIME_FORMATTER = DateTimeFormatter.ofPattern("HH:mm:ss", Locale.ENGLISH);

    private final SimpleConsoleManager consoleManager;

    @SneakyThrows
    public SimpleLogger() {
        this.consoleManager = new SimpleConsoleManager(this);

        System.setOut(new PrintStream(new LoggerOutputStream(this, LogType.INFO), true));
        System.setErr(new PrintStream(new LoggerOutputStream(this, LogType.ERROR), true));
    }

    @Override
    public String format(@NotNull String text, @NotNull LogType logType) {
        String message = "§r" + text + "§r";

        if (logType != LogType.EMPTY) {
            String currentTime = LocalTime.now().format(TIME_FORMATTER);
            String separator = isWindows() ? "|" : "┃";
            String indicator = isWindows() ? ">" : "»";

            message = String.format(
                " %s §7%s §r%s %s §r%s§r",
                currentTime, separator, logType.getTextField(), indicator, message
            );
        }

        return LoggerAnsiFactory.toColorCode(message);
    }

    @Override
    public void log(@NotNull String text, @NotNull LogType logType) {
        this.log(new String[]{text}, logType);
    }

    @Override
    public void log(@NotNull String[] text, @NotNull LogType logType) {
        var terminal = consoleManager.getTerminal();

        if (terminal == null) {
            fallbackLog(text, logType);
            return;
        }

        terminal.puts(InfoCmp.Capability.carriage_return);
        for (String line : text) {
            terminal.writer().println(this.format(line, logType));
        }
        terminal.flush();
        consoleManager.redraw();
    }

    @Override
    public void log(@NotNull String... text) {
        this.log(text, LogType.INFO);
    }

    public boolean isWindows() {
        return System.getProperty("os.name").toLowerCase().contains("windows");
    }

    private void fallbackLog(@NotNull String[] text, @NotNull LogType logType) {
        PrintStream fallbackStream = logType == LogType.ERROR ? System.err : System.out;
        for (String line : text) {
            fallbackStream.println(LoggerAnsiFactory.toColorCode(line));
        }
    }
}
