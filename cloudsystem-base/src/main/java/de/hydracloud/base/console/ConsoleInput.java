package de.hydracloud.base.console;

import java.util.List;
import java.util.function.Consumer;

public record ConsoleInput(
    Consumer<String> input,
    List<String> tabCompletions) {
}
