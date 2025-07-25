package de.hydracloud.database.sql.mysql;

import de.hydracloud.database.DatabaseConfiguration;
import de.hydracloud.database.sql.SQLDatabaseProvider;

import java.sql.DriverManager;
import java.sql.SQLException;

public final class MySQLDatabaseProvider extends SQLDatabaseProvider {

    public MySQLDatabaseProvider(final DatabaseConfiguration config) throws SQLException {
        super(DriverManager.getConnection("jdbc:mysql://" + config.getHostname() + ":"
            + config.getPort() + "/" + config.getDatabase()
            + "?user=" + config.getUsername() + "&password=" + config.getPassword() + "&serverTimezone=UTC&autoReconnect=true"));
    }

}
