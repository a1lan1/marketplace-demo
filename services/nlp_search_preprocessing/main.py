from fastapi import FastAPI
from pydantic import BaseModel
import logging

logging.basicConfig(level=logging.INFO, format='%(asctime)s - %(name)s - %(levelname)s - %(message)s')
logger = logging.getLogger(__name__)

app = FastAPI()

class SearchQuery(BaseModel):
    query: str

@app.post("/preprocess_query")
async def preprocess_query(search_query: SearchQuery):
    logger.info(f"Received query for preprocessing: {search_query.query}")
    # For now, a very simple preprocessing: just lowercasing and stripping whitespace
    processed_query = search_query.query.lower().strip()
    logger.info(f"Processed query: {processed_query}")
    return {"processed_query": processed_query}

@app.get("/")
async def root():
    return {"message": "NLP Search Preprocessing Service is running!"}
