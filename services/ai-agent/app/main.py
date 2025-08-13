from fastapi import FastAPI


def create_app() -> FastAPI:
    app = FastAPI(title="SETRAG AI Agent Service")

    @app.get("/health")
    def health() -> dict:
        return {"status": "ok"}

    return app


app = create_app()


