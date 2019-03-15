## Radish Separate WC Fields
This plugin separates the address field in Woocommerce with:
- A street name
- A street number
- A suffix

The data is saved separately, as well as combined in the original address_1 meta field.

## Development
Make sure to follow the following steps when contributing:
1. Clone the repo `git clone git@github.com:radishconcepts/separate-wc-address.git`
2. Run `composer install`
3. Run `npm install`
4. Run `gulp init`

Step 4 will initiate some standards, like creating a symlink to the .hooks/pre-commit file so that a code sniffer is ran before committing.

## Dev Features
- Codesniffer / PHP Coding Standards
- Composer autoload
- Includes pre-commit hook that will be activated with `gulp init`