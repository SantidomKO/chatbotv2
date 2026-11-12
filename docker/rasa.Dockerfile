FROM python:3.10-slim

WORKDIR /app

RUN apt-get update \
    && apt-get install -y --no-install-recommends libgomp1 \
    && rm -rf /var/lib/apt/lists/* \
    && pip install --no-cache-dir "rasa==3.6.21"

COPY config.yml credentials.yml endpoints.docker.yml ./
COPY domain/ ./domain/
COPY data/ ./data/
COPY models/ /models/

ENTRYPOINT ["rasa"]
CMD ["run", "--enable-api", "--interface", "0.0.0.0", "--port", "5005", "--model", "/models", "--endpoints", "endpoints.docker.yml"]
