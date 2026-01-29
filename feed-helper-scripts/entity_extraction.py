"""
Entity extraction helper for article import scripts.
"""

from collections import Counter


try:
    import spacy
    import pytextrank
    nlp = spacy.load("en_core_web_sm")
    if not any(pipe_name == "textrank" for pipe_name, _ in nlp.pipeline):
        nlp.add_pipe("textrank")
    SPACY_AVAILABLE = True
    PYTEXTRANK_AVAILABLE = True
except (ImportError, OSError, ValueError):
    SPACY_AVAILABLE = False
    PYTEXTRANK_AVAILABLE = False
    print("⚠ spaCy or PyTextRank not available. Install with: pip install spacy pytextrank && python -m spacy download en_core_web_sm")
    print("  Entity extraction will be skipped.\n")

def extract_entities(text, top_n=5):
    """
    Extract top N named entities from text using spaCy.
    Returns comma-separated string of entities.
    """
    if not SPACY_AVAILABLE or not text:
        return keyword_fallback(text, top_n)
    try:
        doc = nlp(text[:10000])
        # 1. Top PERSON entity (by order of appearance)
        person_entity = None
        stopwords = set(nlp.Defaults.stop_words)
        for ent in doc.ents:
            norm = ent.text.strip().lower()
            if len(norm) > 2 and norm not in stopwords and any(c.isalnum() for c in norm):
                if ent.label_ == 'PERSON':
                    person_entity = norm
                    break
        # 2. Up to 3 PyTextRank phrases or non-PERSON entities (ORG, EVENT, GPE, PRODUCT, LAW)
        phrase_candidates = []
        # PyTextRank phrases first (in order)
        if PYTEXTRANK_AVAILABLE and hasattr(doc._, 'phrases'):
            for phrase in doc._.phrases:
                pnorm = phrase.text.strip().lower()
                if len(pnorm) > 2 and pnorm not in stopwords and any(c.isalnum() for c in pnorm):
                    if pnorm != person_entity and pnorm not in phrase_candidates:
                        phrase_candidates.append(pnorm)
        # Then non-PERSON entities (in order of appearance)
        for ent in doc.ents:
            norm = ent.text.strip().lower()
            if len(norm) > 2 and norm not in stopwords and any(c.isalnum() for c in norm):
                if ent.label_ in {'ORG', 'EVENT', 'GPE', 'PRODUCT', 'LAW'}:
                    if norm != person_entity and norm not in phrase_candidates:
                        phrase_candidates.append(norm)
        tags = []
        if person_entity:
            tags.append(person_entity)
        tags.extend(phrase_candidates[:3])
        if not tags:
            return keyword_fallback(text, top_n)
        return ', '.join(tags)
    except Exception as e:
        print(f"    ⚠ Entity extraction failed: {e}")
        return keyword_fallback(text, top_n)


# Simple keyword extraction fallback
import re
def keyword_fallback(text, top_n=5):
    # Lowercase and tokenize
    words = re.findall(r'\b\w+\b', text.lower())
    # Remove stopwords and short words
    try:
        import spacy
        stopwords = set(spacy.lang.en.stop_words.STOP_WORDS)
    except ImportError:
        stopwords = set()
    keywords = [w for w in words if w not in stopwords and len(w) > 2]
    counts = Counter(keywords)
    # Only include keywords that occur more than once
    top_keywords = [kw for kw, c in counts.most_common() if c > 1][:top_n]
    return ', '.join(top_keywords[:5])

# --- CATEGORY SUGGESTION ---

PREDEFINED_CATEGORIES = [
    "Business", 
    "Politics", 
    "Sports",
    "HBCUs",
    "Health",
    "Education",
    "Technology",
    "Local News",
    "Finance",
    "Obituaries",
    "Travel",
    "Lifestyle",
    "Culture",
    "Faith"
]

def suggest_categories(text, max_categories=2):
    """
    Suggest 1-2 categories from PREDEFINED_CATEGORIES based on keyword/entity overlap with article text.
    Uses Hugging Face zero-shot-classification as a fallback if available.
    Returns a comma-separated string of categories.
    """
    if not text:
        return ''
    # Use extracted entities and keywords
    entities = extract_entities(text, top_n=10)
    entity_set = set([e.strip().lower() for e in entities.split(',') if e.strip()])
    matched = []
    # Simple: match if any entity/keyword is in category name or vice versa
    for cat in PREDEFINED_CATEGORIES:
        cat_l = cat.lower()
        for ent in entity_set:
            if cat_l in ent or ent in cat_l:
                matched.append(cat)
                break
    # If not enough matches, try keyword presence in text
    if not matched:
        for cat in PREDEFINED_CATEGORIES:
            if cat.lower() in text.lower():
                matched.append(cat)
    # If still not enough, try zero-shot-classification
    if not matched:
        try:
            from transformers import pipeline
            classifier = pipeline("zero-shot-classification", model="facebook/bart-large-mnli")
            result = classifier(text, PREDEFINED_CATEGORIES)
            # result['labels'] is sorted by score descending
            matched = result['labels'][:max_categories]
            print(f"    (zero-shot) Category scores: {list(zip(result['labels'], [round(s,3) for s in result['scores']]))}")
        except ImportError:
            print("⚠ transformers not installed. Install with: pip install transformers torch")
        except Exception as e:
            print(f"⚠ zero-shot-classification failed: {e}")
    # If still not enough, just pick the first categories
    if not matched:
        matched = PREDEFINED_CATEGORIES[:max_categories]
    # Limit to max_categories
    return ', '.join(matched[:max_categories])