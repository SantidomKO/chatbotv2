#!/usr/bin/env python3
"""
sync_dashboard_a_rasa.py

Trae las preguntas y respuestas cargadas en el dashboard (vía API) y regenera
los archivos de Rasa correspondientes:
  - domain/dashboard.yml       (respuestas)
  - data/dashboard_nlu.yml     (intents + ejemplos de entrenamiento)
  - data/dashboard_rules.yml   (intent -> respuesta)

No toca los archivos escritos a mano (domain/base.yml, data/nlu.yml, etc.).

USO:
  1. Configurá las variables de entorno (o editá los valores por defecto abajo):
       DASHBOARD_API_URL   -> endpoint que devuelve el listado de Q&A
       DASHBOARD_API_KEY   -> token de autenticación, si tu API lo requiere
  2. Corré: python sync_dashboard_a_rasa.py
  3. (Opcional) pasá --train para que además dispare "rasa train" al final.

FORMATO ESPERADO DE LA API (ajustar según tu esquema real):
  GET {DASHBOARD_API_URL} -> 200 OK
  [
    {
      "id": "123",
      "categoria": "informatica",              # se usa para armar el nombre del intent
      "preguntas": [                           # variantes de la pregunta (mínimo 1)
        "no me anda el wifi",
        "no tengo internet en el laboratorio"
      ],
      "respuesta": "Texto de la respuesta que debe dar el bot..."
    },
    ...
  ]

Si tu API devuelve otros nombres de campo (ej. "question"/"answer" en vez de
"preguntas"/"respuesta"), ajustá la función `normalizar_entrada()` más abajo.
"""

import os
import re
import sys
import subprocess
import unicodedata
from pathlib import Path

import requests
import yaml

# --- Configuración ---------------------------------------------------------

PROYECTO_DIR = Path(__file__).resolve().parent
DOMAIN_DASHBOARD = PROYECTO_DIR / "domain" / "dashboard.yml"
NLU_DASHBOARD = PROYECTO_DIR / "data" / "dashboard_nlu.yml"
RULES_DASHBOARD = PROYECTO_DIR / "data" / "dashboard_rules.yml"

PREFIJO_INTENT = "dashboard_"  # evita choques de nombres con los intents escritos a mano


# --- Utilidades --------------------------------------------------------------

def slugificar(texto: str) -> str:
    """Convierte texto libre en un identificador válido para intent/response (sin tildes/espacios)."""
    texto = unicodedata.normalize("NFKD", texto).encode("ascii", "ignore").decode("ascii")
    texto = texto.lower().strip()
    texto = re.sub(r"[^a-z0-9]+", "_", texto)
    texto = re.sub(r"_+", "_", texto).strip("_")
    return texto or "sin_nombre"


def normalizar_entrada(item: dict) -> dict:
    """
    Adaptá esta función si tu API usa otros nombres de campo.
    Debe devolver: {"id": str, "categoria": str, "preguntas": [str, ...], "respuesta": str}
    """
    return {
        "id": str(item["id"]),
        "categoria": item.get("categoria", "general"),
        "preguntas": item["preguntas"],
        "respuesta": item["respuesta"],
    }


def obtener_datos_dashboard() -> list[dict]:
    headers = {}
    if DASHBOARD_API_KEY:
        headers["Authorization"] = f"Bearer {DASHBOARD_API_KEY}"

    resp = requests.get(DASHBOARD_API_URL, headers=headers, timeout=30)
    resp.raise_for_status()
    datos = resp.json()

    return [normalizar_entrada(item) for item in datos]


# --- Generación de archivos ---------------------------------------------------

def generar_archivos(entradas: list[dict]) -> None:
    intents_domain = {"version": "3.1", "responses": {}}
    nlu_data = {"version": "3.1", "nlu": []}
    rules_data = {"version": "3.1", "rules": []}

    nombres_usados = set()

    for entrada in entradas:
        if not entrada["preguntas"]:
            print(f"[AVISO] Entrada {entrada['id']} sin preguntas de ejemplo, se omite.")
            continue

        base_slug = slugificar(f"{entrada['categoria']}_{entrada['id']}")
        intent_name = f"{PREFIJO_INTENT}{base_slug}"

        if intent_name in nombres_usados:
            print(f"[AVISO] Intent duplicado '{intent_name}', se omite la entrada {entrada['id']}.")
            continue
        nombres_usados.add(intent_name)

        response_name = f"utter_{intent_name}"

        # Respuesta en el domain
        intents_domain["responses"][response_name] = [{"text": entrada["respuesta"]}]

        # Ejemplos de entrenamiento en el nlu
        ejemplos = "\n".join(f"    - {p}" for p in entrada["preguntas"])
        nlu_data["nlu"].append({
            "intent": intent_name,
            "examples": ejemplos + "\n",
        })

        # Regla intent -> respuesta
        rules_data["rules"].append({
            "rule": f"Dashboard: {entrada['categoria']} #{entrada['id']}",
            "steps": [
                {"intent": intent_name},
                {"action": response_name},
            ],
        })

    # Encabezado de aviso para no editar a mano
    encabezado = (
        "# ATENCIÓN: archivo generado automáticamente por sync_dashboard_a_rasa.py\n"
        "# No editar a mano - se sobreescribe en cada sincronización.\n\n"
    )

    DOMAIN_DASHBOARD.write_text(
        encabezado + yaml.dump(intents_domain, allow_unicode=True, sort_keys=False),
        encoding="utf-8",
    )
    NLU_DASHBOARD.write_text(
        encabezado + yaml.dump(nlu_data, allow_unicode=True, sort_keys=False),
        encoding="utf-8",
    )
    RULES_DASHBOARD.write_text(
        encabezado + yaml.dump(rules_data, allow_unicode=True, sort_keys=False),
        encoding="utf-8",
    )

    print(f"[OK] {len(nombres_usados)} preguntas/respuestas sincronizadas.")
    print(f"     -> {DOMAIN_DASHBOARD}")
    print(f"     -> {NLU_DASHBOARD}")
    print(f"     -> {RULES_DASHBOARD}")


def entrenar_rasa() -> None:
    print("[INFO] Disparando 'rasa train'...")
    subprocess.run(
        ["rasa", "train", "--domain", str(PROYECTO_DIR / "domain")],
        cwd=PROYECTO_DIR,
        check=True,
    )


def main() -> None:
    print(f"[INFO] Consultando {DASHBOARD_API_URL} ...")
    try:
        entradas = obtener_datos_dashboard()
    except requests.RequestException as e:
        print(f"[ERROR] No se pudo consultar la API del dashboard: {e}")
        sys.exit(1)

    if not entradas:
        print("[AVISO] La API no devolvió ninguna pregunta/respuesta.")
        return

    generar_archivos(entradas)

    if "--train" in sys.argv:
        entrenar_rasa()
    else:
        print("[INFO] Archivos actualizados. Corré 'rasa train --domain domain/' "
              "cuando quieras (o volvé a llamar a este script con --train).")


if __name__ == "__main__":
    main()