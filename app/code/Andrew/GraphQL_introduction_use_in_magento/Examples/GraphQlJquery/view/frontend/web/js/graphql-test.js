define(['uiComponent', 'jquery'], function (Component, $) {
    'use strict';

    const query = `
                {
                  cmsPage(identifier: "no-route") {
                    identifier
                    url_key
                    title
                    content
                    content_heading
                    page_layout
                    meta_title
                    meta_description
                    meta_keywords
                  }
                }
    `

    return Component.extend({
        defaults: {
            tracks: {
                result: true
            }
        },
        initialize: function () {
            const payload = {
                query,
                variables: {
                    count: 3
                }
            }

            new Promise(function(resolve, reject) {
                $.ajax({
                    method: 'POST',
                    url: '/graphql',
                    data: JSON.stringify(payload),
                    dataType: 'json',
                    contentType: 'application/json'
                })
            }).then(data => {
                this.result = data
            }).catch(console.error)

            return this._super();
        }
    });
})
