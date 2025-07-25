package de.hydracloud.base.console;

import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.logger.Logger;
import de.hydracloud.base.Base;
import de.hydracloud.base.logger.SimpleLogger;
import lombok.Getter;
import org.jline.reader.LineReader;
import org.jline.reader.LineReaderBuilder;
import org.jline.terminal.Terminal;
import org.jline.terminal.TerminalBuilder;
import org.jline.utils.InfoCmp;

import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.util.List;
import java.util.Queue;
import java.util.concurrent.ConcurrentLinkedQueue;
import java.util.function.Consumer;

public final class SimpleConsoleManager {

    private final Logger logger;
    private Thread consoleReadingThread;
    @Getter
    private final Terminal terminal;
    @Getter
    private final LineReader lineReader;
    private final boolean windowsSystem;

    @Getter
    private final Queue<ConsoleInput> inputs;

    public SimpleConsoleManager(Logger logger) throws IOException {
        this.logger = logger;
        this.windowsSystem = isWindows(logger);

        this.terminal = initializeTerminal();
        this.lineReader = initializeLineReader();

        clearConsole();
        this.inputs = new ConcurrentLinkedQueue<>();
    }

    private boolean isWindows(Logger logger) {
        return (logger instanceof SimpleLogger simpleLogger) && simpleLogger.isWindows();
    }

    private Terminal initializeTerminal() throws IOException {
        try {
            return TerminalBuilder.builder()
                .system(true)
                .streams(System.in, System.out)
                .encoding(StandardCharsets.UTF_8)
                .dumb(true)
                .build();
        } catch (IOException e) {
            throw new IOException("Failed to initialize terminal", e);
        }
    }

    private LineReader initializeLineReader() {
        return LineReaderBuilder.builder()
            .terminal(terminal)
            .option(LineReader.Option.DISABLE_EVENT_EXPANSION, true)
            .option(LineReader.Option.AUTO_REMOVE_SLASH, false)
            .option(LineReader.Option.INSERT_TAB, false)
            .completer(new ConsoleCompleter(this))
            .build();
    }

    public void start() {
        if (consoleReadingThread != null && consoleReadingThread.isAlive()) {
            logger.log("Console reading thread is already running.", LogType.WARNING);
            return;
        }
        this.consoleReadingThread = new ConsoleReadingThread(logger, this, windowsSystem);
        this.consoleReadingThread.setUncaughtExceptionHandler((t, e) -> e.printStackTrace());
        this.consoleReadingThread.start();
    }

    public void clearConsole() {
        terminal.puts(InfoCmp.Capability.clear_screen);
        terminal.flush();
        redraw();
    }

    public void redraw() {
        if (lineReader.isReading()) {
            lineReader.callWidget(LineReader.REDRAW_LINE);
            lineReader.callWidget(LineReader.REDISPLAY);
        }
    }

    public void shutdown() {
        try {
            shutdownReading();
        } finally {
            if (terminal != null) {
                try {
                    terminal.close();
                } catch (IOException e) {
                    System.err.println("Error while closing the terminal: " + e.getMessage());
                    e.printStackTrace();
                }
            }
        }
    }

    public void shutdownReading() {
        if (consoleReadingThread != null && consoleReadingThread.isAlive()) {
            consoleReadingThread.interrupt();
            try {
                consoleReadingThread.join(1000);
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
            }
        }
    }


    public void addInput(Consumer<String> input, List<String> tabCompletions) {
        inputs.add(new ConsoleInput(input, tabCompletions));
    }
}
