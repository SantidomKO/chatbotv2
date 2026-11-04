FROM python:3.10-slim

WORKDIR /app

COPY actions/actions.py ./actions.py

RUN pip install --no-cache-dir "rasa-sdk==3.6.2" "requests==2.34.2"

ENTRYPOINT ["python", "-m", "rasa_sdk"]
CMD ["--actions", "actions", "--port", "5055"]
