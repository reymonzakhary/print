import re


class HelperService:
    def __init__(self):
        pass

    def escape_redisearch_text(self, query: str) -> str:
        """
        Sanitize query to match RediSearch TEXT behavior by preserving important special characters.
        """
        query = query.lower()
        # Keep letters, numbers, spaces, and important special characters like /, -, _, .
        query = re.sub(r'[^\w\s\/\-_\.]', '', query)  # Keep letters, numbers, spaces, /, -, _, .
        # Normalize multiple spaces
        query = re.sub(r'\s+', ' ', query).strip()
        return query