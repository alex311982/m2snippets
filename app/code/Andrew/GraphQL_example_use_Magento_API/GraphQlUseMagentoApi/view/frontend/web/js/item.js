define(['uiComponent'],
    function (Component) {
    'use strict';

    const width = 200, height = 330,
    ItemComponent = Component.extend({
        defaults: {
            template: 'Itdelight_GraphQlUseMagentoApi/item',
            width,
            height
        },
        initConfig: function (options) {
            this._super(options)
            this.items = options.items
            return this
        }
    })

    ItemComponent.width = width
    ItemComponent.height = height

    return ItemComponent;
})
