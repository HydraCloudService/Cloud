package de.hydracloud.base.console;

import de.hydracloud.base.Base;
import de.hydracloud.api.logger.LogType;
import de.hydracloud.api.logger.Logger;
import org.jline.reader.LineReader;

public final class ConsoleReadingThread extends Thread {

    private final String consolePrompt;
    private final SimpleConsoleManager consoleManager;
    private final LineReader lineReader;

    public ConsoleReadingThread(final Logger logger, final SimpleConsoleManager consoleManager, final boolean windows) {
        super("Cloud-Console-Thread");
        this.consoleManager = consoleManager;
        this.lineReader = this.consoleManager.getLineReader();
        this.consolePrompt = logger.format("§fHydra§bCloud §7" + (windows ? ">" : "»") + " §f", LogType.EMPTY);
    }

    @Override
    public void run() {
        try {
            while (!Thread.currentThread().isInterrupted() && this.isAlive()) {
                try {
                    String line = lineReader.readLine(consolePrompt);
                    if (line != null && !line.isBlank()) {
                        processInput(line);
                    }
                } catch (Exception e) {
                    Base.getInstance().getLogger().log("Error reading line: " + e.getMessage(), LogType.ERROR);
                }
            }
        } catch (Exception e) {
            logError(e);
        } finally {
            cleanup();
        }
    }

    private void processInput(String line) {
        var input = consoleManager.getInputs().poll();
        if (input != null) {
            input.input().accept(line);
        } else {
            Base.getInstance().getCommandManager().execute(line);
        }
    }

    private void logError(Exception e) {
        var logger = Base.getInstance().getLogger();
        if (logger != null) {
            logger.log("Unexpected error in console thread: " + e.getMessage(), LogType.ERROR);
            logger.log("Stacktrace:", LogType.ERROR);
            for (StackTraceElement element : e.getStackTrace()) {
                logger.log(element.toString(), LogType.ERROR);
            }
        } else {
            e.printStackTrace();
        }
    }

    private void cleanup() {
        Base.getInstance().getLogger().log("Console thread shutting down...", LogType.INFO);
    }
}
