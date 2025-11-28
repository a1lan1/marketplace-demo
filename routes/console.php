<?php

Schedule::command('backup:run')->daily()->at('08:00');
Schedule::command('backup:clean')->daily()->at('20:00');
