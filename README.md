# LifeJobs
[PMMP] LifeJobs System

##Support API for developers.

```php
use pju6791\LifeJobs\LifeJobs; 
```
After declaration

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
