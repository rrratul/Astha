# Soundscape-BD Dataset Structure Diagram

This repository contains a comprehensive draw.io diagram that visualizes the structure of the Soundscape-BD dataset, a complete urban sound classification dataset for Bangladesh.

## Diagram File

- **File**: `soundscape-bd-dataset-structure.drawio`
- **Format**: Draw.io XML format
- **Purpose**: Visual representation of the dataset folder hierarchy and organization

## Dataset Structure Overview

The Soundscape-BD dataset is organized into a hierarchical structure with four main top-level folders:

### 1. 📁 audio/
Contains raw audio recordings organized by sound classes:
- **rickshaw/** - Traditional rickshaw sounds (1,000 .wav files)
- **car/** - Private vehicle sounds (1,000 .wav files)  
- **bike/** - Motorcycle sounds (1,000 .wav files)
- **construction/** - Construction site sounds (1,000 .wav files)
- **cng/** - CNG auto-rickshaw sounds (1,000 .wav files)
- **bus/** - Public bus sounds (1,000 .wav files)
- **truck/** - Heavy truck sounds (1,000 .wav files)
- **ambulance/** - Emergency vehicle sounds (1,000 .wav files)
- **police/** - Police vehicle sounds (1,000 .wav files)
- **factory/** - Industrial sounds (1,000 .wav files)
- **mixed/** - Combined urban sounds (1,000 .wav files)

### 2. 📁 metadata/
Contains annotations and metadata files:
- **labels.csv** - Class labels mapping (sound_id, class, location)
- **annotations.json** - Detailed metadata (timestamps, coordinates)
- **dataset_info.txt** - Collection details (recording specs, devices)

### 3. 📁 preprocessed/
Contains processed features and representations:
- **spectrograms/** - Visual representations (.png files)
- **mfcc/** - Feature vectors (.npy files)
- **normalized/** - Volume normalized audio (.wav files)

### 4. 📁 documentation/
Contains dataset documentation:
- **README.md** - Dataset overview and usage instructions
- **research_paper.pdf** - Academic publication with methodology & results
- **LICENSE** - Usage terms and attribution requirements

## Dataset Statistics

- **Total Audio Files**: 10,000
- **Audio Classes**: 11 (10 individual classes + 1 mixed class)
- **Files per Class**: 1,000
- **Format**: 16-bit WAV
- **Sample Rate**: 44.1 kHz
- **Total Duration**: ~10 hours
- **Estimated File Size**: ~2.5 GB

## File Format Legend

- 🎵 **.wav** - Raw audio recordings
- 📊 **.csv** - Tabular metadata
- 🔧 **.json** - Structured metadata
- 🧮 **.npy** - NumPy feature arrays
- 🖼️ **.png** - Spectrogram images
- 📄 **.pdf/.md** - Documentation

## How to Use the Diagram

1. Open the `soundscape-bd-dataset-structure.drawio` file in:
   - [Draw.io online editor](https://app.diagrams.net/)
   - Draw.io desktop application
   - Visual Studio Code with Draw.io extension

2. The diagram uses a hierarchical tree structure with:
   - **Color coding** for different folder types
   - **Icons** to represent file types
   - **Connection lines** showing relationships
   - **Statistics boxes** with file counts and details

## Purpose

This diagram serves as a visual guide for:
- Researchers working with urban sound classification
- Data scientists organizing audio datasets
- Students learning about dataset structure
- Documentation of the Soundscape-BD project

The clear hierarchical visualization makes it easy to understand the dataset organization and locate specific files or folders within the structure.