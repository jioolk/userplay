For performace reasons, we do not load all the services all the time - e.g. Router is useless for CLI ( at least now ) 

    |-- README.MD         <- youre here
    |-- common.neon       <- shared for all usecases
    |-- parameters.neon
    |-- cli               <- configuration exclusive for CLI use
    |   `-- cli.neon
    `-- www               <- exclusives for www, like latte etc
        `-- www.neon

TODO: separate configuration for UI and API, needs update of Router