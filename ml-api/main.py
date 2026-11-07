from fastapi import FastAPI, HTTPException
from fastapi.middleware.cors import CORSMiddleware
import tensorflow as tf
from tensorflow.keras.preprocessing.image import img_to_array
from PIL import Image
import numpy as np
import io
import base64

app = FastAPI(title="Smart Waste ML API")

app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

model = tf.keras.models.load_model("waste_model.h5")
LABELS = ['Sampah Organik', 'Sampah Kertas', 'Sampah Plastik'] 

@app.post("/predict")
async def predict_waste(data: dict):
    try:
        # Terima data berupa base64 dari aplikasi mobile
        image_base64 = data.get("image")
        if not image_base64:
            raise HTTPException(status_code=400, detail="No image provided")
        
        # Decode base64 menjadi bytes gambar
        image_bytes = base64.b64decode(image_base64)
        image = Image.open(io.BytesIO(image_bytes)).convert("RGB")
        image = image.resize((224, 224))
        
        img_array = img_to_array(image) / 255.0
        img_array = np.expand_dims(img_array, axis=0)
        
        predictions = model.predict(img_array)
        class_idx = np.argmax(predictions[0])
        confidence = float(predictions[0][class_idx])
        
        return {
            "success": True,
            "category": LABELS[class_idx],
            "confidence": round(confidence * 100, 2)
        }
    except Exception as e:
        return {"success": False, "error": str(e)}