"""
Entity extraction helper for article import scripts.
"""

from collections import Counter

try:
    import spacy
    nlp = spacy.load("en_core_web_sm")
    SPACY_AVAILABLE = True
except (ImportError, OSError):
    SPACY_AVAILABLE = False
    print("⚠ spaCy not available. Install with: pip install spacy && python -m spacy download en_core_web_sm")
    print("  Entity extraction will be skipped.\n")

def extract_entities(text, top_n=5):
    """
    Extract top N named entities from text using spaCy.
    Returns comma-separated string of entities.
    """
    if not SPACY_AVAILABLE or not text:
        return ""
    try:
        doc = nlp(text[:10000])
        entity_types = {'PERSON', 'ORG', 'GPE', 'EVENT', 'PRODUCT', 'LAW'}
        stopwords = set(nlp.Defaults.stop_words)
        entities = []
        for ent in doc.ents:
            if ent.label_ in entity_types:
                norm = ent.text.strip().lower()
                # Remove stopwords and very short entities
                if len(norm) > 2 and norm not in stopwords:
                    # Remove punctuation-only entities
                    if any(c.isalnum() for c in norm):
                        entities.append(norm)
        # Deduplicate while preserving order
        seen = set()
        deduped = []
        for e in entities:
            if e not in seen:
                seen.add(e)
                deduped.append(e)
        entity_counts = Counter(deduped)
        top_entities = [entity for entity, count in entity_counts.most_common(top_n)]
        return ', '.join(top_entities)
    except Exception as e:
        print(f"    ⚠ Entity extraction failed: {e}")
        return ""