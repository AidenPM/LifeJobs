# LifeJobs
[PMMP] LifeJobs System

## Support API for developers.

* After declaration
```php
use pju6791\LifeJobs\LifeJobs; 
```

* get myJob
```php
LifeJobs::getInstance()->myLifeJobs(Player $player);
```

* LifeJobsRegistration
```php
LifeJobs::getInstance()->LifeJobsRegistration(Player $player, string $Job);
```

* LifeJobsWithdrawal
```php
LifeJobs::getInstance()->LifeJobsWithdrawal(Player $player);
```
