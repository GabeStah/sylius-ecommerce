## Console Commands

- Create `src/Command/<NameCommand.php>` file
- Extend `Symfony\Component\Console\Command\Command` class
- Add `configure()` and `execute()` methods, as needed:

```php
namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProductImportCommand extends Command
{
  protected static $defaultName = 'product:import';

  protected function configure() {
    $this->setDescription('Imports a collection of products.');
    $this->setHelp('This command imports a collection of products.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    print_r('Executing ImportProductsCommand');

    // Should return exit status code
    return 0;
  }
}
```

- Clear cache: `php bin/console cache:clear`
- Execute command: `php bin/console product:import`

See: https://symfony.com/doc/current/console.html
