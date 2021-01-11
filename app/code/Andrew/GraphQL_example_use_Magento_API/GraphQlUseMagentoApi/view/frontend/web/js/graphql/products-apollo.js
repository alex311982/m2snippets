define(['apollo-boost'], function (ApolloAmd) {
    'use strict';

    const {ApolloClient, gql} = ApolloAmd;

    const client = new ApolloClient({url: '/graphql'});
    const query = gql(`
        query products($pageSize: Int!){
            products(filter:{} sort:{name: ASC} pageSize:$pageSize) {
                items {
                    id
                    name,
                    url_rewrites{
                        url
                    }
                    image {
                        url
                    }
                }
            }
        }
    `);

    return function (pageSize) {
        return client.query({
            query,
            variables: {
                pageSize
            }
        })
    }
})
