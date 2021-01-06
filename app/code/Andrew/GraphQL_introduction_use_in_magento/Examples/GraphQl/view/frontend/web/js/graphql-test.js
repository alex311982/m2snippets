define(['uiComponent', 'apollo-boost'], function (Component, ApolloAmd) {
    'use strict';

    const {ApolloClient, gql} = ApolloAmd;

    const client = new ApolloClient({url: '/graphql'});
    const query = gql(`
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
    `);

    return Component.extend({
        defaults: {
            tracks: {
                result: true
            }
        },
        initialize: function () {
            client.query({
                query: query,
                variables: {
                    count: 3
                }
            })
                .then(data => {
                    this.result = data
                })
                .catch(console.error)
            return this._super();
        }
    });
})
