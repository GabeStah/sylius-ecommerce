/*
 * This file is part of the Sylius package.
 *
 * (c) Paweł Jędrzejewski
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/* eslint-env browser */

const handleProductOptionsChange = function handleProductOptionsChange() {
  document
    .querySelectorAll('[name*="sylius_add_to_cart[cartItem][variant]"]')
    .forEach(item => {
      item.addEventListener('change', () => {
        let selector = '';

        document
          .querySelectorAll(
            '#sylius-product-adding-to-cart select[data-option]'
          )
          .forEach(element => {
            const select = element;
            const option = select[select.selectedIndex].value;
            selector += `[data-${select.getAttribute(
              'data-option'
            )}="${option}"]`;
          });

        const codeElement = document
          .querySelector('#sylius-variants-codes')
          .querySelector(selector);
        const priceElement = document
          .querySelector('#sylius-variants-pricing')
          .querySelector(selector);
        let code = '';
        let price = '';

        if (codeElement !== null) {
          code = codeElement.getAttribute('data-value');
        }

        if (priceElement !== null) {
          price = priceElement.getAttribute('data-value');
        }

        if (code !== '') {
          document.querySelector('[data-js-product-code]').innerHTML = code;
          document
            .querySelector('button[type=submit]')
            .removeAttribute('disabled');
        } else {
          document.querySelector(
            '[data-js-product-code]'
          ).innerHTML = document
            .querySelector('#sylius-variants-codes')
            .getAttribute('data-unavailable-text');
          document
            .querySelector('button[type=submit]')
            .setAttribute('disabled', 'disabled');
        }

        if (price !== '') {
          document.querySelector('[data-js-product-price]').innerHTML = price;
          document
            .querySelector('button[type=submit]')
            .removeAttribute('disabled');
        } else {
          document.querySelector(
            '[data-js-product-price]'
          ).innerHTML = document
            .querySelector('#sylius-variants-pricing')
            .getAttribute('data-unavailable-text');
          document
            .querySelector('button[type=submit]')
            .setAttribute('disabled', 'disabled');
        }
      });
    });
};

const handleProductVariantsChange = function handleProductVariantsChange() {
  document
    .querySelectorAll('[name="sylius_add_to_cart[cartItem][variant]"]')
    .forEach(item => {
      item.addEventListener('change', e => {
        const price = item
          .closest('tr')
          .querySelector('[data-js-product-variant-price]').innerHTML;
        document.querySelector('[data-js-product-price]').innerHTML = price;
      });
    });
};

const SyliusVariantsPrices = () => {
  const syliusVariantsPricing =
    document.getElementById('sylius-variants-pricing') || null;
  const syliusProductVariants =
    document.getElementById('sylius-product-variants') || null;

  if (syliusVariantsPricing) {
    handleProductOptionsChange();
  } else if (syliusProductVariants) {
    handleProductVariantsChange();
  }
};

export default SyliusVariantsPrices;
