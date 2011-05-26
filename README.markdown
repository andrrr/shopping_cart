# Shopping Cart #

This extension provides shopping cart base functionality 
for your Symphony website.

- Version: 1.2.1
- Date: 26th May 2011
- Requirements: Symphony 2.1.1 or above
- Author: Andrey Lubinov, andrey.lubinov@gmail.com
- Constributors: [Giel Berkers](http://github.com/kanduvisla),[Mario Butera](http://github.com/mblabs)
- GitHub Repository: <http://github.com/mblabs/shopping_cart>

## Installation

1. Upload the 'shopping_cart' folder to your Symphony 'extensions' folder
2. Enable it by selecting "Shopping Cart" in the list, choose Enable from the with-selected menu, then click Apply
3. When creating or editing a section you can add the "Price" field and the "Weight" field

## Usage

#### Event

- Look at "Shopping Cart" Event's description for list of possible options.

#### Data Source

- Look at "Shopping Cart" Data Source's description.

#### Price field output filtering

- Use ` range: {$min-price}/{$max-price} ` for filtering by price ranges. 
- Use ` range: {$min-weight}/{$max-weight} ` for filtering by weight ranges. 