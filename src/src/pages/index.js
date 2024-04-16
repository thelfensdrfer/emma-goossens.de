import React from "react"

const IndexPage = () => {
    return (
        <div className="h-screen p-8">
            <div className="flex flex-col h-full">
                <main className="flex-1">
                    <title>Emma Gooßens</title>
                    <h1 className="text-9xl text-center mt-4 underline decoration-pink-500">Emma Gooßens</h1>
                </main>

                <footer className="text-gray-500 text-right">
                    <p>
                        <a target="_blank" href="https://thelfensdrfer.de/impressum">Impressum</a>&nbsp;|&nbsp;
                        <a target="_blank" href="https://thelfensdrfer.de/datenschutzerklaerung">Datenschutzerklärung</a></p>
                </footer>
            </div>
        </div>
    )
}

export default IndexPage
