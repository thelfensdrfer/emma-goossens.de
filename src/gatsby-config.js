module.exports = {
    siteMetadata: {
        title: `Emma Gooßens`,
        siteUrl: `https://www.emma-goosens.de`
    },
    plugins: [
        "gatsby-plugin-postcss",
        "gatsby-plugin-image",
        "gatsby-plugin-mdx",
        "gatsby-plugin-sharp",
        "gatsby-transformer-sharp",
        {
            resolve: 'gatsby-source-filesystem',
            options: {
                "name": "images",
                "path": "./src/images/"
            },
            __key: "images"
        }, {
            resolve: 'gatsby-source-filesystem',
            options: {
                "name": "pages",
                "path": "./src/pages/"
            },
            __key: "pages"
        }, {
            resolve: `gatsby-plugin-plausible`,
            options: {
                domain: `emma-goossens.de`,
                customDomain: `stats.codigy.de/js/script.js?original=`,
            },
        }]
}
