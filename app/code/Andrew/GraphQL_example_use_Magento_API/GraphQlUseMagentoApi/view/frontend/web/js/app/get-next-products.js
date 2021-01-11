define(['ko', 'Itdelight_GraphQlUseMagentoApi/js/graphql/products-apollo', 'queryProducts'], function (Component) {
    'use strict';

    function createEmptyProduct() {
        return ko.track({
            name: '...',
            url: '',
            image: 'Magento_Catalog/images/product/placeholder/image.jpg'
        })
    }

    function createEmptyProducts() {
        const products = []

        while (n-- > 0) {
            products.push(createEmptyProduct())
        }

        return products
    }

    function updateProduct(product, productData) {
        product.name = productData.name
        product.url = '/' + productData.url_rewrites[0].url
        product.image = productData.image.url
    }

    return function (n) {
        const products = createEmptyProducts(n)

        queryProducts(n).then(result => {
            result.data.products.items.forEach(productData => updateProduct(products.shift(), productData))
        })


        return products
    }
})
