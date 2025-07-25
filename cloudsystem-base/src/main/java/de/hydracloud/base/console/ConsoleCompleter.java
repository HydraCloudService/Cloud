package de.hydracloud.base.console;

import de.hydracloud.base.Base;
import org.jline.reader.Candidate;
import org.jline.reader.Completer;
import org.jline.reader.LineReader;
import org.jline.reader.ParsedLine;

import java.util.ArrayList;
import java.util.List;
import java.util.Objects;
import java.util.Optional;
import java.util.stream.Collectors;

public record ConsoleCompleter(SimpleConsoleManager consoleManager) implements Completer {

    /**
     * Adds tab completion suggestions for the given parsed line.
     *
     * @param lineReader the line reader instance
     * @param parsedLine the parsed input line
     * @param candidates the list of candidates to populate with suggestions
     */
    @Override
    public void complete(LineReader lineReader, ParsedLine parsedLine, List<Candidate> candidates) {
        String input = parsedLine.line().trim();
        String[] arguments = input.split(" ");
        List<String> suggestions = new ArrayList<>();

        Optional.ofNullable(consoleManager.getInputs().peek())
            .ifPresentOrElse(
                consoleInput -> suggestions.addAll(consoleInput.tabCompletions()),
                () -> suggestions.addAll(generateCommandSuggestions(input, arguments))
            );

        suggestions.stream()
            .filter(Objects::nonNull)
            .distinct()
            .map(Candidate::new)
            .forEach(candidates::add);
    }

    /**
     * Generates command suggestions based on the given input and arguments.
     *
     * @param input     the entire input line
     * @param arguments the split arguments from the input
     * @return a list of suggestions
     */
    private List<String> generateCommandSuggestions(String input, String[] arguments) {
        if (input.isEmpty() || input.indexOf(' ') == -1) {
            return suggestMatchingCommands(arguments[arguments.length - 1]);
        }

        String commandName = arguments[0];
        String[] commandArgs = prepareArguments(input, arguments);

        return Optional.ofNullable(Base.getInstance()
                .getCommandManager()
                .getCachedCloudCommands()
                .get(commandName))
            .map(command -> command.tabComplete(commandArgs))
            .orElseGet(ArrayList::new);
    }

    /**
     * Suggests commands matching the given prefix.
     *
     * @param prefix the prefix to match
     * @return a list of matching command names
     */
    private List<String> suggestMatchingCommands(String prefix) {
        String lowerCasePrefix = prefix.toLowerCase();
        return Base.getInstance()
            .getCommandManager()
            .getCachedCloudCommands()
            .keySet()
            .stream()
            .filter(cmd -> cmd.toLowerCase().startsWith(lowerCasePrefix))
            .sorted()
            .collect(Collectors.toList());
    }

    /**
     * Prepares the arguments for command tab completion.
     *
     * @param input     the entire input line
     * @param arguments the split arguments from the input
     * @return an array of prepared arguments
     */
    private String[] prepareArguments(String input, String[] arguments) {
        boolean endsWithSpace = input.endsWith(" ");
        int startIndex = 1;
        int endIndex = endsWithSpace ? arguments.length + 1 : arguments.length;

        String[] preparedArgs = java.util.Arrays.copyOfRange(arguments, startIndex, endIndex);

        if (endsWithSpace) {
            preparedArgs[preparedArgs.length - 1] = "";
        }

        return preparedArgs;
    }
}
